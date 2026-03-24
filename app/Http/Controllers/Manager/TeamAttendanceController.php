<?php

namespace App\Http\Controllers\Manager;

use App\Helpers\IdCrypt;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\SiteSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TeamAttendanceController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request)
    {
        $manager  = $this->currentUser();
        $minHours = (float) SiteSetting::get('min_work_hours', 9);
        $today    = now()->toDateString();

        $teamMemberIds = $this->teamMemberIds($manager->id);

        // Dropdowns for filters
        $teamMembers = User::whereIn('id', $teamMemberIds)->orderBy('name')->get();
        $deptIds     = $teamMembers->pluck('department_id')->filter()->unique()->values();
        $departments = Department::whereIn('id', $deptIds)->orderBy('name')->get();

        // ── Today's KPI ────────────────────────────────────────────────────────
        $todayAtts = Attendance::whereIn('user_id', $teamMemberIds)
            ->where('date', $today)
            ->get();

        $presentToday = $todayAtts->where('status', 'present')->count();
        $leaveToday   = $todayAtts->whereIn('status', ['leave', 'half_day'])->count();
        $absentToday  = $todayAtts->where('status', 'absent')->count()
                      + (count($teamMemberIds) - $todayAtts->count());

        $insuffToday = $todayAtts->where('status', 'present')->filter(function ($a) use ($minHours) {
            if ($a->work_hours === null) {
                return false;
            }
            return ((float) $a->work_hours + (float) ($a->permission_hours ?? 0)) < $minHours;
        })->count();

        $kpi = [
            'total'   => count($teamMemberIds),
            'present' => $presentToday,
            'absent'  => $absentToday,
            'leave'   => $leaveToday,
            'insuff'  => $insuffToday,
        ];

        // ── Attendance query ───────────────────────────────────────────────────
        [$from, $to, $rangeLabel] = $this->resolveRange($request, now());

        $query = Attendance::whereIn('user_id', $teamMemberIds)
            ->with(['user.department', 'punchLogs' => fn ($q) => $q->orderBy('punched_at')])
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()]);

        // Filter: employee
        if ($encId = $request->input('employee_id')) {
            $eid = IdCrypt::decode($encId);
            if ($eid) {
                $query->where('user_id', $eid);
            }
        }

        // Filter: department
        if ($encDept = $request->input('department')) {
            $deptId = IdCrypt::decode($encDept);
            if ($deptId) {
                $query->whereHas('user', fn ($q) => $q->where('department_id', $deptId));
            }
        }

        // Filter: status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $records = $query
            ->orderByDesc('date')
            ->orderBy('user_id')
            ->paginate(self::PER_PAGE)
            ->withQueryString()
            ->through(function ($att) use ($minHours) {
                $logs    = $att->punchLogs;
                $firstIn = $logs->firstWhere('type', 'in');
                $lastOut = $logs->filter(fn ($l) => $l->type === 'out')->last();

                $att->punch_in_loc  = $firstIn ? $this->fmtLocation($firstIn) : null;
                $att->punch_out_loc = $lastOut ? $this->fmtLocation($lastOut)  : null;
                $att->hours_fmt     = $att->work_hours !== null ? $this->fmtHours((float) $att->work_hours) : null;

                if ($att->status === 'present' && $att->work_hours !== null) {
                    $effective          = (float) $att->work_hours + (float) ($att->permission_hours ?? 0);
                    $att->hours_status  = $effective >= $minHours ? 'sufficient' : 'insufficient';
                    $att->effective_fmt = $this->fmtHours($effective);
                } else {
                    $att->hours_status  = null;
                    $att->effective_fmt = null;
                }

                return $att;
            });

        return view('manager.team-attendance.index', [
            'records'     => $records,
            'kpi'         => $kpi,
            'teamMembers' => $teamMembers,
            'departments' => $departments,
            'minHours'    => $minHours,
            'rangeLabel'  => $rangeLabel,
            'filterType'  => $request->input('filter_type', 'month'),
            'selYear'     => (int) $request->input('year',  now()->year),
            'selMonth'    => (int) $request->input('month', now()->month),
            'dateFrom'    => $request->input('date_from', ''),
            'dateTo'      => $request->input('date_to',   ''),
        ]);
    }

    // ── Private helpers ────────────────────────────────────────────────────────

    private function resolveRange(Request $request, Carbon $now): array
    {
        $filterType = $request->input('filter_type', 'month');

        if ($filterType === 'range' && $request->filled(['date_from', 'date_to'])) {
            $from = Carbon::parse($request->input('date_from'))->startOfDay();
            $to   = Carbon::parse($request->input('date_to'))->endOfDay();
            if ($from->gt($to)) {
                [$from, $to] = [$to, $from];
            }
            if ($from->diffInDays($to) > 365) {
                $to = $from->copy()->addDays(364)->endOfDay();
            }
            $label = $from->format('d M Y') . ' – ' . $to->format('d M Y');
        } else {
            $year  = max(2000, min(2100, (int) $request->input('year',  $now->year)));
            $month = max(1,    min(12,   (int) $request->input('month', $now->month)));
            $from  = Carbon::create($year, $month, 1)->startOfDay();
            $to    = $from->copy()->endOfMonth()->endOfDay();
            $label = $from->format('F Y');
        }

        return [$from, $to, $label];
    }

    private function fmtLocation($log): ?string
    {
        if (! empty($log->formatted_address)) {
            return $log->formatted_address;
        }
        $parts = array_filter([$log->suburb ?? null, $log->city ?? null]);
        return $parts ? implode(', ', $parts) : ($log->location_label ?: null);
    }

    private function fmtHours(float $h): string
    {
        $hrs = (int) floor($h);
        $min = (int) round(($h - $hrs) * 60);
        return "{$hrs}h {$min}m";
    }

    private function teamMemberIds(int $managerId): array
    {
        return User::where(function ($q) use ($managerId) {
            $q->where('level1_manager_id', $managerId)
              ->orWhere('level2_manager_id', $managerId);
        })->where('emp_status', 'active')->pluck('id')->toArray();
    }
}
