<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayListController extends Controller
{
    public function index(Request $request)
    {
        $user  = $this->currentUser();
        $today = now()->toDateString();

        // Base scope: holidays visible to this user (all departments or their own)
        $scopeFilter = function ($q) use ($user) {
            $q->where('scope', 'all');
            if ($user->department_id) {
                $q->orWhere(function ($q2) use ($user) {
                    $q2->where('scope', 'department')
                       ->where('department_id', $user->department_id);
                });
            }
        };

        $request->validate([
            'year'  => ['nullable', 'integer', 'between:2000,2100'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'type'  => ['nullable', 'in:national,optional'],
        ]);

        $query = Holiday::active()->where($scopeFilter);

        if ($year = $request->input('year')) {
            $query->whereYear('date', (int) $year);
        }
        if ($month = $request->input('month')) {
            $query->whereMonth('date', (int) $month);
        }
        if ($type = $request->input('type')) {
            $query->where('type', $type);
        }

        // Upcoming first (distance from today ASC), then past holidays
        $query
            ->orderByRaw("CASE WHEN `date` >= ? THEN 0 ELSE 1 END", [$today])
            ->orderByRaw(
                "CASE WHEN `date` >= ? THEN DATEDIFF(`date`, ?) ELSE DATEDIFF(?, `date`) END",
                [$today, $today, $today]
            );

        $holidays = $query->paginate(20)->withQueryString();

        // Next upcoming holiday (unfiltered by form, always reflects true next holiday)
        $nextHoliday = Holiday::active()
            ->where('date', '>=', $today)
            ->where($scopeFilter)
            ->orderBy('date')
            ->first();

        // Distinct years for year filter dropdown
        $years = Holiday::active()
            ->where($scopeFilter)
            ->selectRaw('YEAR(`date`) as year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('holidays.index', compact('holidays', 'nextHoliday', 'years'));
    }
}
