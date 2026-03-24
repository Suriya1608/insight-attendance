<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\OptionalHolidaySetting;
use App\Models\OptionalHolidaySelection;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TeamOptionalHolidayController extends Controller
{
    public function index(Request $request)
    {
        $manager = auth()->user();
        $year    = (int) $request->get('year', now()->year);

        // Team members under this manager
        $teamMembers = User::where(function ($q) use ($manager) {
                $q->where('level1_manager_id', $manager->id)
                  ->orWhere('level2_manager_id', $manager->id);
            })
            ->where('emp_status', 'active')
            ->orderBy('name')
            ->get();

        $teamIds = $teamMembers->pluck('id');

        // All selections for the team for the given year, with holiday details
        $selections = OptionalHolidaySelection::with(['user', 'holiday'])
            ->whereIn('user_id', $teamIds)
            ->where('year', $year)
            ->orderBy('selected_at', 'desc')
            ->get();

        // Group by user for summary (all statuses shown, used = active only)
        $byMember = $selections->groupBy('user_id');

        $setting    = OptionalHolidaySetting::forYear($year);
        $maxAllowed = $setting?->max_allowed ?? 0;

        // Build team summary rows with per-member pro-rata eligibility
        $teamSummary = $teamMembers->map(function ($member) use ($byMember, $maxAllowed, $setting, $year) {
            $memberSelections = $byMember->get($member->id, collect());
            $activeCount      = $memberSelections->where('status', 'active')->count();
            $eligibleCount    = OptionalHolidaySetting::getEligibleCount($member, $year, $setting);
            $isProRata        = $setting && $member->doj && $member->doj->year === $year;
            return [
                'user'          => $member,
                'used'          => $activeCount,
                'eligible'      => $eligibleCount,
                'max'           => $maxAllowed,
                'remaining'     => max(0, $eligibleCount - $activeCount),
                'isProRata'     => $isProRata,
                'selections'    => $memberSelections,
            ];
        });

        // Available years for filter
        $years = OptionalHolidaySetting::orderByDesc('year')->pluck('year');
        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('manager.team-optional-holidays.index', compact(
            'teamSummary',
            'selections',
            'setting',
            'maxAllowed',
            'year',
            'years'
        ));
    }
}
