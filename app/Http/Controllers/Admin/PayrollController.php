<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Holiday;
use App\Models\LeaveRequest;
use App\Models\OptionalHolidaySelection;
use App\Models\Payroll;
use App\Models\PayrollBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{
    // ── List (index) ──────────────────────────────────────────────────────────

    public function index()
    {
        $batches = PayrollBatch::with(['generatedBy', 'lockedBy'])
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->get();

        $latestBatchId = $batches->first()?->id;

        return view('admin.payroll.index', compact('batches', 'latestBatchId'));
    }

    // ── Generate form + preview ───────────────────────────────────────────────

    public function generate(Request $request)
    {
        $request->validate([
            'month'         => ['nullable', 'integer', 'min:1', 'max:12'],
            'year'          => ['nullable', 'integer', 'min:2020', 'max:2099'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'employee_id'   => ['nullable', 'exists:users,id'],
            'page'          => ['nullable', 'integer', 'min:1'],
        ]);

        $departments = Department::active()->orderBy('name')->get();
        $employees   = User::whereIn('role', ['employee', 'manager'])
                           ->where('emp_status', 'active')
                           ->orderBy('name')
                           ->get();

        $month  = (int) ($request->input('month', now()->month));
        $year   = (int) ($request->input('year',  now()->year));
        $deptId = $request->input('department_id') ? (int) $request->input('department_id') : null;
        $empId  = $request->input('employee_id')   ? (int) $request->input('employee_id')   : null;

        $rows          = collect();
        $existingBatch = null;
        $isFuture      = false;
        $prevNotLocked = false;

        if ($request->hasAny(['month', 'year', 'department_id', 'employee_id']) || $request->has('generate')) {
            $selectedDate = Carbon::create($year, $month, 1)->startOfMonth();
            $isFuture     = $selectedDate->gt(now()->startOfMonth());

            if (!$isFuture) {
                $existingBatch = PayrollBatch::where('month', $month)->where('year', $year)->first();

                // Check if previous month is generated but not locked
                $prevBatch = PayrollBatch::previousBatch($month, $year);
                $prevNotLocked = $prevBatch && !$prevBatch->isLocked();

                $rows = $this->computePayroll($month, $year, $deptId, $empId);
            }
        }

        // Paginate in-memory
        $perPage     = 25;
        $currentPage = max(1, (int) $request->input('page', 1));
        $total       = $rows->count();
        $paginated   = $rows->forPage($currentPage, $perPage)->values();

        return view('admin.payroll.generate', compact(
            'departments', 'employees',
            'month', 'year', 'deptId', 'empId',
            'rows', 'paginated', 'total', 'perPage', 'currentPage',
            'existingBatch', 'isFuture', 'prevNotLocked'
        ));
    }

    // ── Store (save + batch creation) ─────────────────────────────────────────

    public function store(Request $request)
    {
        $request->validate([
            'month'         => ['required', 'integer', 'min:1', 'max:12'],
            'year'          => ['required', 'integer', 'min:2020', 'max:2099'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'employee_id'   => ['nullable', 'exists:users,id'],
        ]);

        $month  = (int) $request->month;
        $year   = (int) $request->year;
        $deptId = $request->department_id ? (int) $request->department_id : null;
        $empId  = $request->employee_id   ? (int) $request->employee_id   : null;

        // 1. Prevent future month
        $selectedDate = Carbon::create($year, $month, 1)->startOfMonth();
        if ($selectedDate->gt(now()->startOfMonth())) {
            return back()->with('error', 'Cannot generate payroll for a future month.');
        }

        // 2. Prevent duplicate generation
        if (PayrollBatch::where('month', $month)->where('year', $year)->exists()) {
            $label = Carbon::create($year, $month)->format('F Y');
            return back()->with('error', "Payroll for {$label} has already been generated. To make changes, edit individual rows from the payroll list.");
        }

        // 3. Auto-lock previous month if it exists and is not locked
        $prevBatch = PayrollBatch::previousBatch($month, $year);
        if ($prevBatch && !$prevBatch->isLocked()) {
            $prevBatch->update([
                'status'    => PayrollBatch::STATUS_LOCKED,
                'locked_by' => Auth::id(),
                'locked_at' => now(),
            ]);
            AuditLog::record(
                module:   'Payroll',
                action:   'auto_lock',
                recordId: $prevBatch->id,
                userId:   Auth::id(),
                newValue: ['month' => $prevBatch->month, 'year' => $prevBatch->year, 'reason' => 'Auto-locked when next month payroll was generated'],
            );
        }

        // 4. Compute & save payroll rows
        $rows = $this->computePayroll($month, $year, $deptId, $empId);

        if ($rows->isEmpty()) {
            return back()->with('error', 'No active employees found for the selected criteria.');
        }

        $savedCount  = 0;
        $totalPayout = 0.0;

        foreach ($rows as $row) {
            Payroll::updateOrCreate(
                [
                    'employee_id' => $row['employee']->id,
                    'month'       => $month,
                    'year'        => $year,
                ],
                [
                    'total_days'              => $row['total_days'],
                    'effective_days'          => $row['effective_days'],
                    'working_days'            => $row['working_days'],
                    'present_days'            => $row['present_days'],
                    'lop_days'                => $row['lop_days'],
                    'permission_hours'        => $row['permission_hours'],
                    'optional_holidays_taken' => $row['optional_holidays_taken'],
                    'salary'                  => $row['salary'],
                    'per_day_salary'          => $row['per_day_salary'],
                    'lop_amount'              => $row['lop_amount'],
                    'net_salary'              => $row['net_salary'],
                    'generated_by'            => Auth::id(),
                ]
            );
            $totalPayout += $row['net_salary'];
            $savedCount++;
        }

        // 5. Create batch record
        $batch = PayrollBatch::create([
            'month'           => $month,
            'year'            => $year,
            'status'          => PayrollBatch::STATUS_GENERATED,
            'total_employees' => $savedCount,
            'total_payout'    => $totalPayout,
            'generated_by'    => Auth::id(),
        ]);

        $monthLabel = Carbon::create($year, $month)->format('F Y');

        AuditLog::record(
            module:   'Payroll',
            action:   'generate',
            recordId: $batch->id,
            userId:   Auth::id(),
            newValue: [
                'month'       => $monthLabel,
                'saved_count' => $savedCount,
                'total_payout'=> $totalPayout,
                'department'  => $deptId,
                'employee'    => $empId,
            ],
        );

        return redirect()->route('admin.payroll.index')
            ->with('success', "Payroll for {$monthLabel} generated successfully for {$savedCount} employee(s). Total payout: ₹" . number_format($totalPayout, 2));
    }

    // ── Lock ─────────────────────────────────────────────────────────────────

    public function lock(int $month, int $year)
    {
        $batch = PayrollBatch::where('month', $month)->where('year', $year)->firstOrFail();

        if ($batch->isLocked()) {
            return back()->with('error', "Payroll for {$batch->month_year_label} is already locked.");
        }

        $batch->update([
            'status'    => PayrollBatch::STATUS_LOCKED,
            'locked_by' => Auth::id(),
            'locked_at' => now(),
        ]);

        AuditLog::record(
            module:   'Payroll',
            action:   'lock',
            recordId: $batch->id,
            userId:   Auth::id(),
            newValue: ['month' => $month, 'year' => $year],
        );

        return back()->with('success', "Payroll for {$batch->month_year_label} has been locked. No further edits are allowed.");
    }

    // ── Edit individual row ───────────────────────────────────────────────────

    public function edit(int $id)
    {
        $payroll = Payroll::with(['employee.department'])->findOrFail($id);

        $batch = PayrollBatch::where('month', $payroll->month)
                             ->where('year', $payroll->year)
                             ->firstOrFail();

        // Only the latest unlocked batch can be edited
        if ($batch->isLocked()) {
            return redirect()->route('admin.payroll.index')
                ->with('error', 'This payroll is locked and cannot be edited.');
        }

        if (!$batch->isLatest()) {
            return redirect()->route('admin.payroll.index')
                ->with('error', 'Only the most recent payroll month can be edited.');
        }

        return view('admin.payroll.edit', compact('payroll', 'batch'));
    }

    // ── Update individual row ─────────────────────────────────────────────────

    public function update(Request $request, int $id)
    {
        $request->validate([
            'working_days' => ['required', 'numeric', 'min:0', 'max:31'],
            'lop_days'     => ['required', 'numeric', 'min:0', 'max:31'],
        ]);

        $payroll = Payroll::with('employee')->findOrFail($id);
        $batch   = PayrollBatch::where('month', $payroll->month)
                               ->where('year', $payroll->year)
                               ->firstOrFail();

        if ($batch->isLocked()) {
            return redirect()->route('admin.payroll.index')
                ->with('error', 'This payroll is locked and cannot be edited.');
        }

        if (!$batch->isLatest()) {
            return redirect()->route('admin.payroll.index')
                ->with('error', 'Only the most recent payroll month can be edited.');
        }

        $oldValues = [
            'working_days' => $payroll->working_days,
            'lop_days'     => $payroll->lop_days,
            'lop_amount'   => $payroll->lop_amount,
            'net_salary'   => $payroll->net_salary,
        ];

        // Recalculate
        $workingDays  = (float) $request->working_days;
        $lopDays      = (float) $request->lop_days;
        $effectiveDays = $payroll->effective_days ?: $payroll->total_days;
        $perDaySalary = $payroll->total_days > 0
            ? round($payroll->salary / $payroll->total_days, 4)
            : 0.0;
        $earnedSalary = round($perDaySalary * $effectiveDays, 2);
        $lopAmount    = round($perDaySalary * $lopDays, 2);
        $netSalary    = round($earnedSalary - $lopAmount, 2);

        $payroll->update([
            'working_days'  => $workingDays,
            'lop_days'      => $lopDays,
            'per_day_salary'=> $perDaySalary,
            'lop_amount'    => $lopAmount,
            'net_salary'    => $netSalary,
        ]);

        // Refresh batch totals
        $newTotal = Payroll::where('month', $payroll->month)->where('year', $payroll->year)->sum('net_salary');
        $batch->update(['total_payout' => $newTotal]);

        AuditLog::record(
            module:   'Payroll',
            action:   'edit',
            recordId: $payroll->id,
            userId:   $payroll->employee_id,
            oldValue: $oldValues,
            newValue: [
                'working_days' => $workingDays,
                'lop_days'     => $lopDays,
                'lop_amount'   => $lopAmount,
                'net_salary'   => $netSalary,
            ],
        );

        return redirect()->route('admin.payroll.index')
            ->with('success', "Payroll for {$payroll->employee->name} updated successfully.");
    }

    // ── Export CSV ────────────────────────────────────────────────────────────

    public function exportCsv(Request $request)
    {
        $request->validate([
            'month'         => ['required', 'integer', 'min:1', 'max:12'],
            'year'          => ['required', 'integer', 'min:2020', 'max:2099'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'employee_id'   => ['nullable', 'exists:users,id'],
        ]);

        $month  = (int) $request->month;
        $year   = (int) $request->year;
        $deptId = $request->department_id ? (int) $request->department_id : null;
        $empId  = $request->employee_id   ? (int) $request->employee_id   : null;

        $rows       = $this->computePayroll($month, $year, $deptId, $empId);
        $monthLabel = Carbon::create($year, $month)->format('F_Y');
        $filename   = "payroll_{$monthLabel}.csv";

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Employee ID', 'Employee Name', 'Department',
                'Total Days', 'Working Days', 'Present Days', 'Paid Leaves',
                'LOP Days', 'Permission Hours', 'Optional Holidays Taken',
                'Monthly Salary (INR)', 'Per Day Salary (INR)',
                'LOP Amount (INR)', 'Net Salary (INR)',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row['employee']->employee_code,
                    $row['employee']->name,
                    $row['employee']->department?->name ?? '—',
                    $row['total_days'],
                    $row['working_days'],
                    number_format($row['present_days'], 1),
                    number_format($row['paid_leaves'], 1),
                    number_format($row['lop_days'], 2),
                    number_format($row['permission_hours'], 2),
                    $row['optional_holidays_taken'],
                    number_format($row['salary'], 2),
                    number_format($row['per_day_salary'], 2),
                    number_format($row['lop_amount'], 2),
                    number_format($row['net_salary'], 2),
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Print View ────────────────────────────────────────────────────────────

    public function print(Request $request)
    {
        $request->validate([
            'month'         => ['required', 'integer', 'min:1', 'max:12'],
            'year'          => ['required', 'integer', 'min:2020', 'max:2099'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'employee_id'   => ['nullable', 'exists:users,id'],
        ]);

        $month  = (int) $request->month;
        $year   = (int) $request->year;
        $deptId = $request->department_id ? (int) $request->department_id : null;
        $empId  = $request->employee_id   ? (int) $request->employee_id   : null;

        $rows       = $this->computePayroll($month, $year, $deptId, $empId);
        $monthLabel = Carbon::create($year, $month)->format('F Y');

        return view('admin.payroll.print', compact('rows', 'monthLabel', 'month', 'year'));
    }

    // ── Core computation ──────────────────────────────────────────────────────

    private function computePayroll(
        int $month,
        int $year,
        ?int $departmentId = null,
        ?int $employeeId   = null
    ): Collection {
        $start     = Carbon::create($year, $month, 1)->startOfDay();
        $end       = $start->copy()->endOfMonth()->endOfDay();
        $totalDays = $start->daysInMonth;

        $nationalHolidays = Holiday::active()
            ->where('type', 'national')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        $optionalHolidayIds = Holiday::active()
            ->where('type', 'optional')
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->pluck('id');

        $query = User::whereIn('role', ['employee', 'manager'])
            ->where('emp_status', 'active')
            ->with('department')
            ->orderBy('name');

        if ($departmentId) $query->where('department_id', $departmentId);
        if ($employeeId)   $query->where('id', $employeeId);

        $employees = $query->get();
        $empIds    = $employees->pluck('id');

        $allAttendances = Attendance::whereIn('user_id', $empIds)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy('user_id');

        $allLeaveRequests = LeaveRequest::whereIn('user_id', $empIds)
            ->where('status', 'approved')
            ->where('request_type', 'leave')
            ->where(function ($q) use ($start, $end) {
                $q->whereBetween('from_date', [$start->toDateString(), $end->toDateString()])
                  ->orWhereBetween('to_date', [$start->toDateString(), $end->toDateString()])
                  ->orWhere(function ($q2) use ($start, $end) {
                      $q2->where('from_date', '<=', $start->toDateString())
                         ->where('to_date', '>=', $end->toDateString());
                  });
            })
            ->get()
            ->groupBy('user_id');

        $allOptSelections = $optionalHolidayIds->isNotEmpty()
            ? OptionalHolidaySelection::whereIn('user_id', $empIds)
                ->whereIn('holiday_id', $optionalHolidayIds)
                ->where('status', 'active')
                ->get()
                ->groupBy('user_id')
            : collect();

        $results = [];

        foreach ($employees as $employee) {
            // ── DOJ mid-month adjustment ──────────────────────────────────────
            // If the employee's joining date falls inside the current payroll
            // month, earnings start from that date — all prior days (including
            // weekends and holidays) are excluded from the calculation.
            $doj             = $employee->doj; // Carbon|null (cast on model)
            $joiningAdjusted = false;
            $effectiveStart  = $start->copy();

            if (
                $doj
                && (int) $doj->format('n') === $month
                && (int) $doj->format('Y') === $year
                && $doj->gt($start)
            ) {
                $effectiveStart  = $doj->copy()->startOfDay();
                $joiningAdjusted = true;
            }

            // Count calendar days and Sundays from the effective start date
            $effectiveDays    = 0;
            $effectiveSundays = 0;
            for ($d = $effectiveStart->copy(); $d->lte($end); $d->addDay()) {
                $effectiveDays++;
                if ($d->isSunday()) $effectiveSundays++;
            }

            // National holidays within the effective window, scoped to dept
            $effectiveNatHolidays = $nationalHolidays->filter(function ($h) use ($employee, $effectiveStart) {
                if ($h->scope === 'department' && $h->department_id !== $employee->department_id) {
                    return false;
                }
                return $h->date->gte($effectiveStart);
            })->count();

            $workingDays = max(0, $effectiveDays - $effectiveSundays - $effectiveNatHolidays);

            // ── Attendance & leave ────────────────────────────────────────────
            $attendances     = $allAttendances->get($employee->id, collect());
            $presentCount    = $attendances->where('status', 'present')->count();
            $halfDayCount    = $attendances->where('status', 'half_day')->count();
            $presentDays     = $presentCount + ($halfDayCount * 0.5);
            $permissionHours = (float) $attendances->sum('permission_hours');

            $paidLeaves            = (float) $allLeaveRequests->get($employee->id, collect())->sum('cl_days');
            $optionalHolidaysTaken = $allOptSelections->get($employee->id, collect())->count();

            // ── Salary calculation ────────────────────────────────────────────
            // Per-day rate is always based on the full calendar month so the
            // daily rate is consistent across all employees.
            // Earned salary is pro-rated to the effective window for mid-month
            // joiners; for regular employees it equals the full monthly salary.
            $lopDays       = max(0.0, round($workingDays - ($presentDays + $paidLeaves + $optionalHolidaysTaken), 2));
            $monthlySalary = (float) $employee->salary;
            $perDaySalary  = $totalDays > 0 ? round($monthlySalary / $totalDays, 4) : 0.0;
            $earnedSalary  = round($perDaySalary * $effectiveDays, 2);
            $lopAmount     = round($perDaySalary * $lopDays, 2);
            $netSalary     = round($earnedSalary - $lopAmount, 2);

            $results[] = [
                'employee'                => $employee,
                'total_days'              => $totalDays,
                'effective_days'          => $effectiveDays,
                'joining_adjusted'        => $joiningAdjusted,
                'working_days'            => $workingDays,
                'present_days'            => $presentDays,
                'paid_leaves'             => $paidLeaves,
                'lop_days'                => $lopDays,
                'permission_hours'        => $permissionHours,
                'optional_holidays_taken' => $optionalHolidaysTaken,
                'salary'                  => $monthlySalary,
                'per_day_salary'          => $perDaySalary,
                'lop_amount'              => $lopAmount,
                'net_salary'              => $netSalary,
            ];
        }

        return collect($results);
    }
}
