<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Holiday;
use App\Models\SiteSetting;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class AttendanceController extends Controller
{
    private const PER_PAGE = 31;

    // ── Index ──────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $user     = $this->currentUser();
        $now      = Carbon::now();
        $minHours = (float) SiteSetting::get('min_work_hours', 9);

        [$from, $to, $rangeLabel] = $this->resolveRange($request, $now);
        [$days, $kpi]             = $this->buildDays($user, $from, $to, $now, $minHours);

        $page      = max(1, (int) $request->query('page', 1));
        $slice     = array_slice($days, ($page - 1) * self::PER_PAGE, self::PER_PAGE);
        $paginator = new LengthAwarePaginator(
            $slice, count($days), self::PER_PAGE, $page,
            ['path' => $request->url(), 'query' => $request->except('page')]
        );

        $regularizationCreateRoute = $user->isManager()
            ? 'manager.my-regularizations.create'
            : 'employee.regularizations.create';

        return view('attendance.index', [
            'paginator'                  => $paginator,
            'kpi'                        => $kpi,
            'minHours'                   => $minHours,
            'rangeLabel'                 => $rangeLabel,
            'filterType'                 => $request->input('filter_type', 'month'),
            'selYear'                    => (int) $request->input('year',  $now->year),
            'selMonth'                   => (int) $request->input('month', $now->month),
            'dateFrom'                   => $request->input('date_from', $from->toDateString()),
            'dateTo'                     => $request->input('date_to',   $to->toDateString()),
            'regularizationCreateRoute'  => $regularizationCreateRoute,
        ]);
    }

    // ── Export ─────────────────────────────────────────────────────────────────

    public function export(Request $request, string $format)
    {
        $user     = $this->currentUser();
        $now      = Carbon::now();
        $minHours = (float) SiteSetting::get('min_work_hours', 9);

        [$from, $to, $rangeLabel] = $this->resolveRange($request, $now);
        [$days, $kpi]             = $this->buildDays($user, $from, $to, $now, $minHours);

        if ($format === 'csv') {
            return $this->exportCsv($days, $rangeLabel, $user->name);
        }

        // PDF: print-optimised HTML page
        return view('attendance.export-pdf', compact('days', 'kpi', 'rangeLabel', 'minHours', 'user'));
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private function resolveRange(Request $request, Carbon $now): array
    {
        $filterType = $request->input('filter_type', 'month');

        if ($filterType === 'range' && $request->filled(['date_from', 'date_to'])) {
            $request->validate([
                'date_from' => ['required', 'date_format:Y-m-d'],
                'date_to'   => ['required', 'date_format:Y-m-d'],
            ]);
            $from = Carbon::createFromFormat('Y-m-d', $request->input('date_from'))->startOfDay();
            $to   = Carbon::createFromFormat('Y-m-d', $request->input('date_to'))->endOfDay();
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

    private function buildDays($user, Carbon $from, Carbon $to, Carbon $now, float $minHours): array
    {
        $attendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->with(['punchLogs' => fn($q) => $q->orderBy('punched_at')])
            ->get()
            ->keyBy(fn($a) => $a->date->format('Y-m-d'));

        $holidayMap = Holiday::active()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->where(function ($q) use ($user) {
                $q->where('scope', 'all')
                  ->orWhere(function ($q2) use ($user) {
                      $q2->where('scope', 'department')
                         ->where('department_id', $user->department_id);
                  });
            })
            ->get()
            ->keyBy(fn($h) => $h->date->format('Y-m-d'));

        $days        = [];
        $workDays    = 0;
        $presentDays = 0;
        $absentDays  = 0;
        $missedDays  = 0;
        $totalHours  = 0.0;
        $insuffDays  = 0;

        foreach (CarbonPeriod::create($from->toDateString(), $to->toDateString()) as $date) {
            $dateStr   = $date->toDateString();
            $isSunday  = $date->dayOfWeek === Carbon::SUNDAY;
            $isHoliday = isset($holidayMap[$dateStr]);
            $isFuture  = $date->gt($now->copy()->endOfDay());
            $att       = $attendances->get($dateStr);

            if ($isSunday) {
                $rowStatus = 'sunday';
            } elseif ($isHoliday) {
                $rowStatus = 'holiday';
            } elseif ($isFuture) {
                $rowStatus = 'future';
            } elseif ($att) {
                $rowStatus = $att->status;
            } else {
                $rowStatus = 'absent';
            }

            if (!$isSunday && !$isHoliday && !$isFuture) {
                $workDays++;
                if ($rowStatus === 'present') {
                    $presentDays++;
                } elseif ($rowStatus === 'absent') {
                    $absentDays++;
                } elseif ($rowStatus === 'missed_punch_out') {
                    $missedDays++;
                }
            }

            $punchIn        = null;
            $punchOut       = null;
            $punchInLoc     = null;
            $punchOutLoc    = null;
            $workHours      = null;
            $permHours      = null;
            $effectiveHours = null;
            $hoursStatus    = null;

            if ($att) {
                $punchIn  = $att->punch_in  ? substr((string) $att->punch_in,  0, 5) : null;
                $punchOut = $att->punch_out ? substr((string) $att->punch_out, 0, 5) : null;
                $workHours = $att->work_hours ? (float) $att->work_hours : null;
                $permHours = $att->permission_hours > 0 ? (float) $att->permission_hours : null;

                $logs    = $att->punchLogs;
                $firstIn = $logs->firstWhere('type', 'in');
                $lastOut = $logs->filter(fn($l) => $l->type === 'out')->last();

                $punchInLoc  = $firstIn ? $this->fmtLocation($firstIn)  : null;
                $punchOutLoc = $lastOut ? $this->fmtLocation($lastOut)   : null;

                if ($rowStatus === 'present' && $workHours !== null) {
                    $effectiveHours = $workHours + ($permHours ?? 0);
                    $totalHours    += $workHours;
                    if ($effectiveHours < $minHours) {
                        $insuffDays++;
                        $hoursStatus = 'insufficient';
                    } else {
                        $hoursStatus = 'sufficient';
                    }
                }
            }

            $days[] = [
                'date'            => $dateStr,
                'day'             => $date->format('l'),
                'row_status'      => $rowStatus,
                'is_holiday'      => $isHoliday,
                'holiday_name'    => $isHoliday ? $holidayMap[$dateStr]->name : null,
                'is_future'       => $isFuture,
                'punch_in'        => $punchIn,
                'punch_out'       => $punchOut,
                'punch_in_loc'    => $punchInLoc,
                'punch_out_loc'   => $punchOutLoc,
                'work_hours'      => $workHours,
                'permission_hours'=> $permHours,
                'effective_hours' => $effectiveHours,
                'hours_status'    => $hoursStatus,
                'hours_fmt'       => $workHours !== null ? $this->fmtHours($workHours) : null,
                'effective_fmt'   => $effectiveHours !== null ? $this->fmtHours($effectiveHours) : null,
                'att_id'          => $att?->id,
            ];
        }

        $kpi = [
            'work_days'   => $workDays,
            'present'     => $presentDays,
            'absent'      => $absentDays,
            'missed'      => $missedDays,
            'total_hours' => round($totalHours, 1),
            'insuff_days' => $insuffDays,
            'hours_fmt'   => $this->fmtHours($totalHours),
        ];

        return [$days, $kpi];
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

    private function exportCsv(array $days, string $rangeLabel, string $userName)
    {
        $filename = 'attendance_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $rangeLabel) . '.csv';

        return response()->streamDownload(function () use ($days, $userName, $rangeLabel) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM for Excel

            fputcsv($out, ['Attendance History', $userName, $rangeLabel]);
            fputcsv($out, []);
            fputcsv($out, [
                'Date', 'Day', 'Status',
                'Punch In', 'Punch Out',
                'Punch In Location', 'Punch Out Location',
                'Work Hours', 'Permission Hours', 'Effective Hours', 'Hours Status',
            ]);

            foreach ($days as $d) {
                fputcsv($out, [
                    Carbon::parse($d['date'])->format('d M Y'),
                    $d['day'],
                    ucwords(str_replace('_', ' ', $d['row_status'])),
                    $d['punch_in']      ?? '—',
                    $d['punch_out']     ?? '—',
                    $d['punch_in_loc']  ?? '—',
                    $d['punch_out_loc'] ?? '—',
                    $d['hours_fmt']     ?? '—',
                    $d['permission_hours'] ? $d['permission_hours'] . 'h' : '—',
                    $d['effective_fmt'] ?? $d['hours_fmt'] ?? '—',
                    $d['hours_status']  ? ucfirst($d['hours_status']) . ' Hours' : '—',
                ]);
            }

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
