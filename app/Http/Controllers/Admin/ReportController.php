<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRegularization;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Payroll;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    // ── Shared helpers ─────────────────────────────────────────────────────────

    private function baseFilters(Request $request): array
    {
        $request->validate([
            'from'          => ['nullable', 'date'],
            'to'            => ['nullable', 'date'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'employee_id'   => ['nullable', 'exists:users,id'],
        ]);

        return [
            'from'   => $request->input('from', now()->startOfMonth()->toDateString()),
            'to'     => $request->input('to',   now()->toDateString()),
            'deptId' => $request->input('department_id') ? (int) $request->input('department_id') : null,
            'empId'  => $request->input('employee_id')   ? (int) $request->input('employee_id')   : null,
        ];
    }

    private function sharedDropdowns(): array
    {
        return [
            'departments' => Department::active()->orderBy('name')->get(),
            'employees'   => User::whereIn('role', ['employee', 'manager'])
                                 ->where('emp_status', 'active')
                                 ->orderBy('name')->get(),
        ];
    }

    // ── Attendance Report ──────────────────────────────────────────────────────

    public function attendance(Request $request)
    {
        ['from' => $from, 'to' => $to, 'deptId' => $deptId, 'empId' => $empId] = $this->baseFilters($request);

        $rows = Attendance::with('user.department')
            ->whereBetween('date', [$from, $to])
            ->when($deptId, fn($q) => $q->whereHas('user', fn($q2) => $q2->where('department_id', $deptId)))
            ->when($empId,  fn($q) => $q->where('user_id', $empId))
            ->orderByDesc('date')
            ->orderBy('user_id')
            ->paginate(25)->withQueryString();

        return view('admin.reports.attendance',
            array_merge($this->sharedDropdowns(), compact('rows', 'from', 'to', 'deptId', 'empId'))
        );
    }

    public function exportAttendanceCsv(Request $request)
    {
        ['from' => $from, 'to' => $to, 'deptId' => $deptId, 'empId' => $empId] = $this->baseFilters($request);

        $rows = Attendance::with('user.department')
            ->whereBetween('date', [$from, $to])
            ->when($deptId, fn($q) => $q->whereHas('user', fn($q2) => $q2->where('department_id', $deptId)))
            ->when($empId,  fn($q) => $q->where('user_id', $empId))
            ->orderByDesc('date')->orderBy('user_id')
            ->get();

        $filename = "attendance_{$from}_to_{$to}.csv";

        return response()->stream(function () use ($rows) {
            $h = fopen('php://output', 'w');
            fwrite($h, "\xEF\xBB\xBF");
            fputcsv($h, ['Emp ID', 'Employee', 'Department', 'Date', 'Status', 'Punch In', 'Punch Out', 'Work Hours', 'Permission Hours']);
            foreach ($rows as $r) {
                fputcsv($h, [
                    $r->user?->employee_code ?? '',
                    $r->user?->name ?? '',
                    $r->user?->department?->name ?? '—',
                    $r->date->format('d-m-Y'),
                    ucfirst(str_replace('_', ' ', $r->status)),
                    $r->punch_in  ? substr($r->punch_in,  0, 5) : '—',
                    $r->punch_out ? substr($r->punch_out, 0, 5) : '—',
                    $r->work_hours        ? number_format($r->work_hours, 2)        : '0',
                    $r->permission_hours  ? number_format($r->permission_hours, 2)  : '0',
                ]);
            }
            fclose($h);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    // ── Missed Punch-Out Report ────────────────────────────────────────────────

    public function missedPunchout(Request $request)
    {
        ['from' => $from, 'to' => $to, 'deptId' => $deptId, 'empId' => $empId] = $this->baseFilters($request);

        $rows = Attendance::with('user.department')
            ->whereBetween('date', [$from, $to])
            ->whereNotNull('punch_in')
            ->whereNull('punch_out')
            ->when($deptId, fn($q) => $q->whereHas('user', fn($q2) => $q2->where('department_id', $deptId)))
            ->when($empId,  fn($q) => $q->where('user_id', $empId))
            ->orderByDesc('date')
            ->paginate(25)->withQueryString();

        return view('admin.reports.missed-punchout',
            array_merge($this->sharedDropdowns(), compact('rows', 'from', 'to', 'deptId', 'empId'))
        );
    }

    public function exportMissedPunchoutCsv(Request $request)
    {
        ['from' => $from, 'to' => $to, 'deptId' => $deptId, 'empId' => $empId] = $this->baseFilters($request);

        $rows = Attendance::with('user.department')
            ->whereBetween('date', [$from, $to])
            ->whereNotNull('punch_in')
            ->whereNull('punch_out')
            ->when($deptId, fn($q) => $q->whereHas('user', fn($q2) => $q2->where('department_id', $deptId)))
            ->when($empId,  fn($q) => $q->where('user_id', $empId))
            ->orderByDesc('date')
            ->get();

        $filename = "missed_punchout_{$from}_to_{$to}.csv";

        return response()->stream(function () use ($rows) {
            $h = fopen('php://output', 'w');
            fwrite($h, "\xEF\xBB\xBF");
            fputcsv($h, ['Emp ID', 'Employee', 'Department', 'Date', 'Punch In', 'Status']);
            foreach ($rows as $r) {
                fputcsv($h, [
                    $r->user?->employee_code ?? '',
                    $r->user?->name ?? '',
                    $r->user?->department?->name ?? '—',
                    $r->date->format('d-m-Y'),
                    $r->punch_in ? substr($r->punch_in, 0, 5) : '—',
                    ucfirst(str_replace('_', ' ', $r->status)),
                ]);
            }
            fclose($h);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    // ── Regularization Report ──────────────────────────────────────────────────

    public function regularization(Request $request)
    {
        $request->validate([
            'from'          => ['nullable', 'date'],
            'to'            => ['nullable', 'date'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'employee_id'   => ['nullable', 'exists:users,id'],
            'status'        => ['nullable', 'string'],
        ]);

        $from   = $request->input('from', now()->startOfMonth()->toDateString());
        $to     = $request->input('to',   now()->toDateString());
        $deptId = $request->input('department_id') ? (int) $request->input('department_id') : null;
        $empId  = $request->input('employee_id')   ? (int) $request->input('employee_id')   : null;
        $status = $request->input('status');

        $rows = AttendanceRegularization::with('user.department')
            ->whereBetween('date', [$from, $to])
            ->when($deptId, fn($q) => $q->whereHas('user', fn($q2) => $q2->where('department_id', $deptId)))
            ->when($empId,  fn($q) => $q->where('user_id', $empId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('date')
            ->paginate(25)->withQueryString();

        $statuses = [
            'draft'      => 'Draft',
            'pending_l1' => 'Pending L1',
            'pending_l2' => 'Pending L2',
            'approved'   => 'Approved',
            'rejected'   => 'Rejected',
        ];

        return view('admin.reports.regularization',
            array_merge($this->sharedDropdowns(), compact('rows', 'from', 'to', 'deptId', 'empId', 'status', 'statuses'))
        );
    }

    public function exportRegularizationCsv(Request $request)
    {
        $request->validate([
            'from'          => ['nullable', 'date'],
            'to'            => ['nullable', 'date'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'employee_id'   => ['nullable', 'exists:users,id'],
            'status'        => ['nullable', 'string'],
        ]);

        $from   = $request->input('from', now()->startOfMonth()->toDateString());
        $to     = $request->input('to',   now()->toDateString());
        $deptId = $request->input('department_id') ? (int) $request->input('department_id') : null;
        $empId  = $request->input('employee_id')   ? (int) $request->input('employee_id')   : null;
        $status = $request->input('status');

        $rows = AttendanceRegularization::with('user.department')
            ->whereBetween('date', [$from, $to])
            ->when($deptId, fn($q) => $q->whereHas('user', fn($q2) => $q2->where('department_id', $deptId)))
            ->when($empId,  fn($q) => $q->where('user_id', $empId))
            ->when($status, fn($q) => $q->where('status', $status))
            ->orderByDesc('date')
            ->get();

        $filename = "regularization_{$from}_to_{$to}.csv";

        return response()->stream(function () use ($rows) {
            $h = fopen('php://output', 'w');
            fwrite($h, "\xEF\xBB\xBF");
            fputcsv($h, ['Emp ID', 'Employee', 'Department', 'Date', 'Type', 'Req. Punch In', 'Req. Punch Out', 'Status', 'Submitted At']);
            foreach ($rows as $r) {
                fputcsv($h, [
                    $r->user?->employee_code ?? '',
                    $r->user?->name ?? '',
                    $r->user?->department?->name ?? '—',
                    $r->date->format('d-m-Y'),
                    $r->type_label,
                    $r->requested_punch_in  ? substr($r->requested_punch_in,  0, 5) : '—',
                    $r->requested_punch_out ? substr($r->requested_punch_out, 0, 5) : '—',
                    $r->status_label,
                    $r->submitted_at?->format('d-m-Y H:i') ?? '—',
                ]);
            }
            fclose($h);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    // ── Payroll Report ─────────────────────────────────────────────────────────

    public function payrollReport(Request $request)
    {
        $request->validate([
            'month'         => ['nullable', 'integer', 'min:1', 'max:12'],
            'year'          => ['nullable', 'integer', 'min:2020', 'max:2099'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'employee_id'   => ['nullable', 'exists:users,id'],
        ]);

        $month  = (int) $request->input('month', now()->month);
        $year   = (int) $request->input('year',  now()->year);
        $deptId = $request->input('department_id') ? (int) $request->input('department_id') : null;
        $empId  = $request->input('employee_id')   ? (int) $request->input('employee_id')   : null;

        $rows = Payroll::with('employee.department')
            ->where('month', $month)->where('year', $year)
            ->when($deptId, fn($q) => $q->whereHas('employee', fn($q2) => $q2->where('department_id', $deptId)))
            ->when($empId,  fn($q) => $q->where('employee_id', $empId))
            ->orderBy('employee_id')
            ->paginate(25)->withQueryString();

        $monthLabel = Carbon::create($year, $month)->format('F Y');

        return view('admin.reports.payroll',
            array_merge($this->sharedDropdowns(), compact('rows', 'month', 'year', 'deptId', 'empId', 'monthLabel'))
        );
    }

    public function exportPayrollCsv(Request $request)
    {
        $request->validate([
            'month'         => ['nullable', 'integer', 'min:1', 'max:12'],
            'year'          => ['nullable', 'integer', 'min:2020', 'max:2099'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'employee_id'   => ['nullable', 'exists:users,id'],
        ]);

        $month  = (int) $request->input('month', now()->month);
        $year   = (int) $request->input('year',  now()->year);
        $deptId = $request->input('department_id') ? (int) $request->input('department_id') : null;
        $empId  = $request->input('employee_id')   ? (int) $request->input('employee_id')   : null;

        $rows = Payroll::with('employee.department')
            ->where('month', $month)->where('year', $year)
            ->when($deptId, fn($q) => $q->whereHas('employee', fn($q2) => $q2->where('department_id', $deptId)))
            ->when($empId,  fn($q) => $q->where('employee_id', $empId))
            ->orderBy('employee_id')->get();

        $filename = 'payroll_' . Carbon::create($year, $month)->format('F_Y') . '.csv';

        return response()->stream(function () use ($rows) {
            $h = fopen('php://output', 'w');
            fwrite($h, "\xEF\xBB\xBF");
            fputcsv($h, [
                'Emp ID', 'Employee', 'Department',
                'Total Days', 'Working Days', 'Present Days',
                'LOP Days', 'Permission Hours', 'Optional Holidays',
                'Gross Salary (INR)', 'Per Day (INR)', 'LOP Amount (INR)', 'Net Salary (INR)',
            ]);
            foreach ($rows as $r) {
                fputcsv($h, [
                    $r->employee?->employee_code ?? '',
                    $r->employee?->name ?? '',
                    $r->employee?->department?->name ?? '—',
                    $r->total_days,
                    $r->working_days,
                    number_format($r->present_days, 1),
                    number_format($r->lop_days, 2),
                    number_format($r->permission_hours, 2),
                    $r->optional_holidays_taken,
                    number_format($r->salary, 2),
                    number_format($r->per_day_salary, 2),
                    number_format($r->lop_amount, 2),
                    number_format($r->net_salary, 2),
                ]);
            }
            fclose($h);
        }, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ]);
    }

    // ── Analytics Dashboard ────────────────────────────────────────────────────

    public function analytics()
    {
        $today      = now()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd   = now()->endOfMonth()->toDateString();

        // ── KPI Cards ─────────────────────────────────────────────────────────

        $totalEmployees = User::whereIn('role', ['employee', 'manager'])
                              ->where('emp_status', 'active')->count();

        $presentToday = Attendance::where('date', $today)
                                  ->whereIn('status', ['present', 'half_day'])->count();

        $absentToday = Attendance::where('date', $today)
                                 ->where('status', 'absent')->count();

        // Late = punch_in after 09:30
        $lateToday = Attendance::where('date', $today)
                               ->where('status', 'present')
                               ->whereTime('punch_in', '>', '09:30:00')
                               ->count();

        $pendingApprovals = LeaveRequest::whereIn('status', ['pending', 'approved_l1'])->count()
                          + AttendanceRegularization::whereIn('status', ['pending_l1', 'pending_l2'])->count();

        // ── Chart 1: Attendance Trend (last 30 days) ──────────────────────────

        $trendStart = now()->subDays(29)->toDateString();
        $trendData  = Attendance::whereBetween('date', [$trendStart, $today])
            ->select(
                'date',
                DB::raw('COUNT(*) as total'),
                DB::raw("SUM(CASE WHEN status IN ('present','half_day') THEN 1 ELSE 0 END) as present_count")
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy(fn($r) => Carbon::parse($r->date)->toDateString());

        $trendLabels  = [];
        $trendPresent = [];
        $trendAbsent  = [];
        for ($i = 29; $i >= 0; $i--) {
            $d   = now()->subDays($i)->toDateString();
            $row = $trendData->get($d);
            $trendLabels[]  = now()->subDays($i)->format('d M');
            $trendPresent[] = $row ? (int) $row->present_count : 0;
            $trendAbsent[]  = $row ? max(0, (int) $row->total - (int) $row->present_count) : 0;
        }

        // ── Chart 2: Department Attendance this month (bar) ───────────────────

        $deptAttendance = Attendance::whereBetween('date', [$monthStart, $today])
            ->whereIn('attendances.status', ['present', 'half_day'])
            ->join('users',       'attendances.user_id',    '=', 'users.id')
            ->join('departments', 'users.department_id',    '=', 'departments.id')
            ->select('departments.name as dept_name', DB::raw('COUNT(*) as present_count'))
            ->groupBy('departments.name')
            ->orderByDesc('present_count')
            ->limit(10)
            ->get();

        $deptLabels = $deptAttendance->pluck('dept_name')->toArray();
        $deptCounts = $deptAttendance->pluck('present_count')->map(fn($v) => (int) $v)->toArray();

        // ── Chart 3: Attendance Rate by Day of Week this month ───────────────────

        $dowData = Attendance::whereBetween('date', [$monthStart, $today])
            ->select(
                DB::raw('DAYOFWEEK(date) as dow'),
                DB::raw("SUM(CASE WHEN status IN ('present','half_day') THEN 1 ELSE 0 END) as present_count"),
                DB::raw('COUNT(DISTINCT date) as working_days'),
                DB::raw('COUNT(DISTINCT user_id) as total_users')
            )
            ->groupBy('dow')
            ->orderBy('dow')
            ->get()
            ->keyBy('dow');

        // DAYOFWEEK: 2=Mon … 6=Fri (skip 1=Sun, 7=Sat)
        $dowNames  = [2 => 'Mon', 3 => 'Tue', 4 => 'Wed', 5 => 'Thu', 6 => 'Fri'];
        $dowLabels = [];
        $dowRates  = [];
        foreach ($dowNames as $num => $label) {
            $row = $dowData->get($num);
            $dowLabels[] = $label;
            if ($row && $row->working_days > 0 && $row->total_users > 0) {
                $possible    = $row->working_days * $row->total_users;
                $dowRates[]  = round(($row->present_count / $possible) * 100, 1);
            } else {
                $dowRates[]  = 0;
            }
        }

        // ── Chart 4: Payroll Cost – last 6 months (bar) ───────────────────────

        $payrollLabels = [];
        $payrollCosts  = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $payrollLabels[] = $d->format('M Y');
            $payrollCosts[]  = (float) Payroll::where('month', $d->month)->where('year', $d->year)->sum('net_salary');
        }

        // ── Productivity Insights ─────────────────────────────────────────────

        $avgWorkHours = round(
            (float) (Attendance::whereBetween('date', [$monthStart, $today])
                ->where('status', 'present')
                ->whereNotNull('work_hours')
                ->avg('work_hours') ?? 0),
            2
        );

        $frequentLate = Attendance::whereBetween('date', [$monthStart, $today])
            ->where('status', 'present')
            ->whereTime('punch_in', '>', '09:30:00')
            ->select('user_id', DB::raw('COUNT(*) as late_count'))
            ->groupBy('user_id')
            ->orderByDesc('late_count')
            ->limit(5)
            ->with('user.department')
            ->get();

        $highLop = Payroll::where('month', now()->month)->where('year', now()->year)
            ->where('lop_days', '>', 0)
            ->with('employee.department')
            ->orderByDesc('lop_days')
            ->limit(5)
            ->get();

        $permUsage = Attendance::whereBetween('date', [$monthStart, $today])
            ->where('permission_hours', '>', 0)
            ->select('user_id', DB::raw('SUM(permission_hours) as total_perm'))
            ->groupBy('user_id')
            ->orderByDesc('total_perm')
            ->limit(5)
            ->with('user.department')
            ->get();

        return view('admin.reports.analytics', compact(
            'totalEmployees', 'presentToday', 'absentToday', 'lateToday', 'pendingApprovals',
            'trendLabels', 'trendPresent', 'trendAbsent',
            'deptLabels', 'deptCounts',
            'dowLabels', 'dowRates',
            'payrollLabels', 'payrollCosts',
            'avgWorkHours', 'frequentLate', 'highLop', 'permUsage'
        ));
    }
}
