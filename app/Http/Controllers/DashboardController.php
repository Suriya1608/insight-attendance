<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveTransaction;
use App\Models\OptionalHolidaySetting;
use App\Models\OptionalHolidaySelection;
use App\Models\PunchLog;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function admin()
    {
        $now   = Carbon::now();
        $today = $now->toDateString();
        $year  = $now->year;

        // — KPI counts —————————————————————————————————————————————————————————
        $totalEmployees = User::where('emp_status', 'active')
            ->whereIn('role', ['employee', 'manager'])
            ->count();

        $totalDepts = \App\Models\Department::where('status', 'active')->count();

        $presentToday = Attendance::where('date', $today)
            ->where('status', 'present')
            ->count();

        $onLeaveToday = LeaveRequest::where('request_type', 'leave')
            ->where('status', 'approved')
            ->where('from_date', '<=', $today)
            ->where('to_date', '>=', $today)
            ->distinct('user_id')
            ->count('user_id');

        $absentToday = max(0, $totalEmployees - $presentToday - $onLeaveToday);

        $pendingLeaves = LeaveRequest::whereIn('status', ['pending', 'approved_l1'])->count();

        $pendingRegularizations = \App\Models\AttendanceRegularization::whereIn('status', ['pending_l1', 'pending_l2'])->count();

        // — Upcoming holidays (next 5) ——————————————————————————————————————————
        $upcomingHolidays = Holiday::active()
            ->where('date', '>=', $today)
            ->where('scope', 'all')
            ->orderBy('date')
            ->limit(5)
            ->get();

        // — Pending leave requests (latest 6) ——————————————————————————————————
        $pendingLeaveRequests = LeaveRequest::with(['user.department'])
            ->whereIn('status', ['pending', 'approved_l1'])
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        // — Recently added employees (latest 6) ————————————————————————————————
        $recentEmployees = User::with('department')
            ->whereIn('role', ['employee', 'manager'])
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        // — Today's attendance breakdown by department ————————————————————————
        $deptBreakdown = \App\Models\Department::where('status', 'active')
            ->withCount([
                'employees as total_employees' => fn ($q) => $q->where('emp_status', 'active'),
            ])
            ->having('total_employees', '>', 0)
            ->orderByDesc('total_employees')
            ->get()
            ->map(function ($dept) use ($today) {
                $dept->present_count = Attendance::whereIn('user_id',
                    User::where('department_id', $dept->id)->where('emp_status', 'active')->pluck('id')
                )->where('date', $today)->where('status', 'present')->count();
                return $dept;
            });

        return view('dashboard.admin', compact(
            'totalEmployees', 'totalDepts', 'presentToday', 'onLeaveToday',
            'absentToday', 'pendingLeaves', 'pendingRegularizations',
            'upcomingHolidays', 'pendingLeaveRequests', 'recentEmployees',
            'deptBreakdown', 'today'
        ));
    }

    public function manager()
    {
        $user  = $this->currentUser();
        $now   = Carbon::now();
        $today = $now->toDateString();
        $year  = $now->year;
        $month = $now->month;

        // —— Leave stats ——————————————————————————————————————————————————————————
        $leave = $this->leaveStats($user->id);

        // —— Today's attendance + punch state —————————————————————————————————————
        $todayAttendance  = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        $punchState       = 'none';
        $punchInTimestamp = null;
        $punchInLocation  = null;
        $punchOutLocation = null;

        if ($todayAttendance) {
            $inCount  = PunchLog::where('attendance_id', $todayAttendance->id)->where('type', 'in')->count();
            $outCount = PunchLog::where('attendance_id', $todayAttendance->id)->where('type', 'out')->count();

            $inLog = PunchLog::where('attendance_id', $todayAttendance->id)
                ->where('type', 'in')->latest('punched_at')
                ->select('formatted_address', 'location_label', 'punched_at')->first();
            $punchInLocation = $inLog
                ? ($inLog->formatted_address ?: $inLog->location_label)
                : null;

            if ($inCount > $outCount) {
                $punchState       = 'in';
                $punchInTimestamp = $inLog
                    ? Carbon::parse($inLog->punched_at)->timestamp * 1000
                    : null;
            } elseif ($inCount > 0) {
                $punchState = 'done';
                $outLog     = PunchLog::where('attendance_id', $todayAttendance->id)
                    ->where('type', 'out')->latest('punched_at')
                    ->select('formatted_address', 'location_label')->first();
                $punchOutLocation = $outLog
                    ? ($outLog->formatted_address ?: $outLog->location_label)
                    : null;
            }
        }

        // —— Personal monthly KPIs —————————————————————————————————————————————————
        $monthAttendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $year)->whereMonth('date', $month)->get();

        $presentDays  = $monthAttendances->where('status', 'present')->count();
        $workHrsMonth = round($monthAttendances->sum('work_hours'), 1);

        // —— Team members (level1 or level2 direct reports) ————————————————————————
        $teamMembers = User::where(function ($q) use ($user) {
                $q->where('level1_manager_id', $user->id)
                  ->orWhere('level2_manager_id', $user->id);
            })
            ->where('emp_status', 'active')
            ->with('department')
            ->get();

        $teamIds = $teamMembers->pluck('id');

        $teamTodayAttendances = Attendance::whereIn('user_id', $teamIds)
            ->where('date', $today)->get()->keyBy('user_id');

        $teamPresentCount = $teamTodayAttendances->where('status', 'present')->count();
        $teamAbsentCount  = max(0, $teamMembers->count() - $teamPresentCount);

        $kpi = [
            'present'      => $presentDays,
            'work_hours'   => $workHrsMonth,
            'team_total'   => $teamMembers->count(),
            'team_present' => $teamPresentCount,
            'team_absent'  => $teamAbsentCount,
        ];

        // —— Team overview for today ————————————————————————————————————————————————
        $teamOverview = $teamMembers->map(function ($member) use ($teamTodayAttendances) {
            $att = $teamTodayAttendances->get($member->id);
            return [
                'user'       => $member,
                'status'     => $att ? $att->status : 'absent',
                'punch_in'   => $att ? substr((string) $att->punch_in, 0, 5) : null,
                'punch_out'  => $att ? substr((string) $att->punch_out, 0, 5) : null,
                'work_hours' => $att ? $att->formatted_work_hours : '—',
            ];
        });

        // —— Upcoming holidays —————————————————————————————————————————————————————
        $upcomingHolidays = Holiday::active()
            ->where('date', '>=', $today)
            ->where(function ($q) use ($user) {
                $q->where('scope', 'all')
                  ->orWhere(function ($q2) use ($user) {
                      $q2->where('scope', 'department')->where('department_id', $user->department_id);
                  });
            })
            ->orderBy('date')->limit(5)->get();

        // —— Today's birthdays —————————————————————————————————————————————————————
        $birthdays = User::whereNotNull('dob')
            ->whereRaw('MONTH(dob) = ? AND DAY(dob) = ?', [$month, $now->day])
            ->where('emp_status', 'active')
            ->whereIn('role', ['employee', 'manager'])
            ->with('department')
            ->get();

        // —— Attendance calendar ————————————————————————————————————————————————————
        $calYear  = (int) request()->query('cal_year',  $year);
        $calMonth = (int) request()->query('cal_month', $month);
        $calData  = $this->buildCalendar($user, $calYear, $calMonth, $today, $now);

        // —— Task stats ————————————————————————————————————————————————————————————
        $taskStats = [
            'total'     => Task::where('assigned_by', $user->id)->count(),
            'pending'   => Task::where('assigned_by', $user->id)->where('status', 'pending')->count(),
            'completed' => Task::where('assigned_by', $user->id)->where('status', 'completed')->count(),
            'overdue'   => Task::where('assigned_by', $user->id)
                ->whereNotIn('status', ['completed'])
                ->where('due_date', '<', $today)
                ->count(),
        ];

        // —— Optional holiday stats (personal, sidebar) ———————————————————————————
        $optSetting  = OptionalHolidaySetting::forYear($year);
        $optUsed     = OptionalHolidaySelection::where('user_id', $user->id)->where('year', $year)->active()->count();
        $optMax      = $optSetting?->max_allowed ?? 0;
        $optStats    = ['max' => $optMax, 'used' => $optUsed, 'remaining' => max(0, $optMax - $optUsed)];

        // —— Tomorrow Leave & Permission (team) ————————————————————————————————————
        $tomorrow = $now->copy()->addDay()->toDateString();

        // CL / LOP / other approved leaves spanning tomorrow
        $tomorrowLeaves = LeaveRequest::with(['user.department'])
            ->whereIn('user_id', $teamIds)
            ->where('request_type', 'leave')
            ->where('status', 'approved')
            ->where('from_date', '<=', $tomorrow)
            ->where('to_date', '>=', $tomorrow)
            ->get()
            ->map(fn ($r) => [
                'user'  => $r->user,
                'type'  => $r->leave_type === 'CL' ? 'CL' : ucfirst(str_replace('_', ' ', $r->leave_type ?? 'Leave')),
                'time'  => 'Full Day',
                'date'  => $tomorrow,
            ]);

        // Optional holidays for tomorrow
        $tomorrowOptRows = OptionalHolidaySelection::with(['user.department', 'holiday'])
            ->whereIn('user_id', $teamIds)
            ->where('year', $year)
            ->active()
            ->whereHas('holiday', fn ($q) => $q->whereDate('date', $tomorrow))
            ->get()
            ->map(fn ($s) => [
                'user'  => $s->user,
                'type'  => 'Optional Holiday',
                'time'  => 'Full Day',
                'date'  => $tomorrow,
            ]);

        // Approved permissions for tomorrow
        $tomorrowPerms = LeaveRequest::with(['user.department'])
            ->whereIn('user_id', $teamIds)
            ->where('request_type', 'permission')
            ->where('status', 'approved')
            ->where('request_date', $tomorrow)
            ->get()
            ->map(fn ($r) => [
                'user'  => $r->user,
                'type'  => 'Permission',
                'time'  => $r->permission_hours . ' hr',
                'date'  => $tomorrow,
            ]);

        $tomorrowAbsences = $tomorrowLeaves
            ->concat($tomorrowOptRows)
            ->concat($tomorrowPerms)
            ->sortBy('user.name')
            ->values();

        return view('dashboard.manager', compact(
            'leave',
            'todayAttendance', 'punchState', 'punchInTimestamp',
            'punchInLocation', 'punchOutLocation',
            'kpi', 'teamOverview',
            'upcomingHolidays', 'birthdays',
            'taskStats',
            'optStats', 'tomorrowAbsences',
        ) + $calData);
    }

    public function managerCalendarData()
    {
        $user  = $this->currentUser();
        $now   = Carbon::now();
        $today = $now->toDateString();

        $calYear  = (int) request()->query('cal_year',  $now->year);
        $calMonth = (int) request()->query('cal_month', $now->month);
        $data     = $this->buildCalendar($user, $calYear, $calMonth, $today, $now);

        return response()->json([
            'calMonthLabel' => $data['calMonthLabel'],
            'firstDayOffs'  => $data['firstDayOffs'],
            'calYear'       => $data['calYear'],
            'calMonth'      => $data['calMonth'],
            'calPrevYear'   => $data['calPrev']->year,
            'calPrevMonth'  => $data['calPrev']->month,
            'calPrevLabel'  => $data['calPrev']->format('F Y'),
            'calNextYear'   => $data['calNext']->year,
            'calNextMonth'  => $data['calNext']->month,
            'calNextLabel'  => $data['calNext']->format('F Y'),
            'days'          => array_values($data['calendarDays']),
        ]);
    }

    public function employee()
    {
        $user  = $this->currentUser();
        $now   = Carbon::now();
        $today = $now->toDateString();
        $year  = $now->year;
        $month = $now->month;

        // —— Leave stats ——————————————————————————————————————————————————————————
        $leave = $this->leaveStats($user->id);

        // —— Today's attendance + punch state —————————————————————————————————————
        $todayAttendance  = Attendance::where('user_id', $user->id)->where('date', $today)->first();

        $punchState       = 'none'; // none | in | done
        $punchInTimestamp = null;
        $punchInLocation  = null;
        $punchOutLocation = null;

        if ($todayAttendance) {
            $inCount  = PunchLog::where('attendance_id', $todayAttendance->id)->where('type', 'in')->count();
            $outCount = PunchLog::where('attendance_id', $todayAttendance->id)->where('type', 'out')->count();

            $inLog = PunchLog::where('attendance_id', $todayAttendance->id)
                ->where('type', 'in')->latest('punched_at')
                ->select('formatted_address', 'location_label', 'punched_at')->first();
            $punchInLocation = $inLog
                ? ($inLog->formatted_address ?: $inLog->location_label)
                : null;

            if ($inCount > $outCount) {
                $punchState       = 'in';
                $punchInTimestamp = $inLog
                    ? Carbon::parse($inLog->punched_at)->timestamp * 1000
                    : null;
            } elseif ($inCount > 0) {
                $punchState = 'done';
                $outLog     = PunchLog::where('attendance_id', $todayAttendance->id)
                    ->where('type', 'out')->latest('punched_at')
                    ->select('formatted_address', 'location_label')->first();
                $punchOutLocation = $outLog
                    ? ($outLog->formatted_address ?: $outLog->location_label)
                    : null;
            }
        }

        // —— Monthly KPIs (always current month) ——————————————————————————————————
        $monthAttendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get();

        $presentDays  = $monthAttendances->where('status', 'present')->count();
        $workHrsMonth = round($monthAttendances->sum('work_hours'), 1);

        $leaveDays = LeaveTransaction::where('user_id', $user->id)
            ->where('leave_type', 'CL')
            ->where('transaction_type', 'debit')
            ->where('year', $year)
            ->where('month', $month)
            ->sum('amount');

        $permUnits = LeaveTransaction::where('user_id', $user->id)
            ->where('leave_type', 'permission')
            ->where('transaction_type', 'debit')
            ->where('year', $year)
            ->where('month', $month)
            ->sum('amount');
        $permHours = $permUnits * 2;

        // Working days elapsed (MonÃ¢â‚¬â€œSat, excl. holidays) up to yesterday for absent KPI
        $kpiHolidayDates = Holiday::active()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where(function ($q) use ($user) {
                $q->where('scope', 'all')
                  ->orWhere(function ($q2) use ($user) {
                      $q2->where('scope', 'department')->where('department_id', $user->department_id);
                  });
            })
            ->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->toArray();

        $workingDaysElapsed = 0;
        $cursor             = $now->copy()->startOfMonth();
        $yesterday          = $now->copy()->subDay()->endOfDay();
        while ($cursor->lte($yesterday)) {
            if ($cursor->dayOfWeek !== Carbon::SUNDAY && ! in_array($cursor->toDateString(), $kpiHolidayDates)) {
                $workingDaysElapsed++;
            }
            $cursor->addDay();
        }

        $absentDays = max(0, $workingDaysElapsed - $presentDays - (int) $leaveDays);

        $kpi = [
            'present'    => $presentDays,
            'absent'     => $absentDays,
            'leave_days' => (float) $leaveDays,
            'perm_hours' => (float) $permHours,
            'work_hours' => $workHrsMonth,
        ];

        // —— Upcoming holidays ————————————————————————————————————————————————————
        $upcomingHolidays = Holiday::active()
            ->where('date', '>=', $today)
            ->where(function ($q) use ($user) {
                $q->where('scope', 'all')
                  ->orWhere(function ($q2) use ($user) {
                      $q2->where('scope', 'department')->where('department_id', $user->department_id);
                  });
            })
            ->orderBy('date')->limit(5)->get();

        // —— Today's birthdays ————————————————————————————————————————————————————
        $birthdays = User::whereNotNull('dob')
            ->whereRaw('MONTH(dob) = ? AND DAY(dob) = ?', [$month, $now->day])
            ->where('emp_status', 'active')
            ->whereIn('role', ['employee', 'manager'])
            ->with('department')
            ->get();

        // —— Attendance calendar (default = current month) ————————————————————————
        $calYear  = (int) request()->query('cal_year',  $year);
        $calMonth = (int) request()->query('cal_month', $month);
        $calData  = $this->buildCalendar($user, $calYear, $calMonth, $today, $now);

        // —— Optional holiday stats ————————————————————————————————————————————————
        $optSetting = OptionalHolidaySetting::forYear($year);
        $optUsed    = OptionalHolidaySelection::where('user_id', $user->id)->where('year', $year)->active()->count();
        $optMax     = $optSetting?->max_allowed ?? 0;
        $optStats   = ['max' => $optMax, 'used' => $optUsed, 'remaining' => max(0, $optMax - $optUsed)];

        return view('dashboard.employee', compact(
            'leave',
            'todayAttendance', 'punchState', 'punchInTimestamp',
            'punchInLocation', 'punchOutLocation',
            'kpi',
            'upcomingHolidays',
            'birthdays',
            'optStats',
        ) + $calData);
    }

    // —— AJAX calendar-data endpoint ————————————————————————————————————————————

    public function calendarData()
    {
        $user  = $this->currentUser();
        $now   = Carbon::now();
        $today = $now->toDateString();

        $calYear  = (int) request()->query('cal_year',  $now->year);
        $calMonth = (int) request()->query('cal_month', $now->month);
        $data     = $this->buildCalendar($user, $calYear, $calMonth, $today, $now);

        return response()->json([
            'calMonthLabel' => $data['calMonthLabel'],
            'firstDayOffs'  => $data['firstDayOffs'],
            'calYear'       => $data['calYear'],
            'calMonth'      => $data['calMonth'],
            'calPrevYear'   => $data['calPrev']->year,
            'calPrevMonth'  => $data['calPrev']->month,
            'calPrevLabel'  => $data['calPrev']->format('F Y'),
            'calNextYear'   => $data['calNext']->year,
            'calNextMonth'  => $data['calNext']->month,
            'calNextLabel'  => $data['calNext']->format('F Y'),
            'days'          => array_values($data['calendarDays']),
        ]);
    }

    // —— Calendar builder helper ————————————————————————————————————————————————

    private function buildCalendar($user, int $calYear, int $calMonth, string $today, Carbon $now): array
    {
        $calYear  = max(2000, min(2100, $calYear));
        $calMonth = max(1, min(12, $calMonth));

        $calBase = Carbon::create($calYear, $calMonth, 1);
        $calPrev = $calBase->copy()->subMonth();
        $calNext = $calBase->copy()->addMonth();

        $calMonthAttendances = Attendance::where('user_id', $user->id)
            ->whereYear('date', $calYear)->whereMonth('date', $calMonth)->get();

        $calHolidayDates = Holiday::active()
            ->whereYear('date', $calYear)->whereMonth('date', $calMonth)
            ->where(function ($q) use ($user) {
                $q->where('scope', 'all')
                  ->orWhere(function ($q2) use ($user) {
                      $q2->where('scope', 'department')->where('department_id', $user->department_id);
                  });
            })
            ->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->toArray();

        $firstDayOffs  = $calBase->dayOfWeek;
        $calDaysInMonth = $calBase->daysInMonth;
        $calMonthAttMap = $calMonthAttendances->keyBy(fn($a) => $a->date->format('Y-m-d'));

        $calendarDays = [];
        for ($d = 1; $d <= $calDaysInMonth; $d++) {
            $date      = Carbon::create($calYear, $calMonth, $d);
            $dateStr   = $date->toDateString();
            $isSunday  = $date->dayOfWeek === Carbon::SUNDAY;
            $isHoliday = in_array($dateStr, $calHolidayDates);
            $isFuture  = $date->gt($now->copy()->endOfDay());
            $isToday   = $dateStr === $today;

            if ($isSunday)        { $status = 'sunday'; }
            elseif ($isHoliday)   { $status = 'holiday'; }
            elseif ($isFuture)    { $status = 'future'; }
            elseif ($isToday)     { $att = $calMonthAttMap->get($dateStr); $status = $att ? $att->status : 'pending'; }
            else                  { $att = $calMonthAttMap->get($dateStr); $status = $att ? $att->status : 'absent'; }

            $calendarDays[$d] = ['date' => $dateStr, 'day' => $d, 'status' => $status, 'isToday' => $isToday];
        }

        return compact(
            'calYear', 'calMonth', 'calBase', 'calPrev', 'calNext',
            'firstDayOffs', 'calDaysInMonth', 'calendarDays'
        ) + ['calMonthLabel' => $calBase->format('F Y')];
    }

    // —— Shared leave stats helper ——————————————————————————————————————————————

    private function leaveStats(int $userId): array
    {
        $now   = Carbon::now();
        $year  = $now->year;
        $month = $now->month;

        $cl = LeaveBalance::where('user_id', $userId)
            ->where('leave_type', 'CL')
            ->where('year', $year)
            ->whereNull('month')
            ->first();

        $perm = LeaveBalance::where('user_id', $userId)
            ->where('leave_type', 'permission')
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        $sat = LeaveBalance::where('user_id', $userId)
            ->where('leave_type', 'saturday_leave')
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        return [
            'cl_credited'   => (float) ($cl?->credited ?? 0),
            'cl_used'       => (float) ($cl?->used     ?? 0),
            'cl_balance'    => $cl ? max(0.0, $cl->credited - $cl->used - $cl->lapsed) : 0.0,

            'perm_credited' => (float) ($perm?->credited ?? 0),
            'perm_used'     => (float) ($perm?->used     ?? 0),
            'perm_balance'  => $perm ? max(0.0, $perm->credited - $perm->used - $perm->lapsed) : 0.0,

            'has_saturday'  => $sat !== null,
            'sat_credited'  => (float) ($sat?->credited ?? 0),
            'sat_used'      => (float) ($sat?->used     ?? 0),
            'sat_balance'   => $sat ? max(0.0, $sat->credited - $sat->used - $sat->lapsed) : 0.0,
        ];
    }
}
