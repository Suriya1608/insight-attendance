<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\Holiday;
use App\Models\OptionalHolidaySetting;
use App\Models\OptionalHolidaySelection;
use Illuminate\Http\Request;

class OptionalHolidayController extends Controller
{
    public function index()
    {
        $user  = auth()->user();
        $year  = now()->year;

        // Active setting for this year
        $setting = OptionalHolidaySetting::forYear($year);

        // Pro-rata eligible count for this employee
        $eligibleCount = OptionalHolidaySetting::getEligibleCount($user, $year, $setting);

        // Full max (for display: "Eligible X / 11")
        $maxAllowed = $setting?->max_allowed ?? 0;

        // Count only ACTIVE selections for this year
        $usedCount = OptionalHolidaySelection::where('user_id', $user->id)
            ->where('year', $year)
            ->active()
            ->count();

        $remaining  = max(0, $eligibleCount - $usedCount);

        // Flag: employee joined this year so pro-rata applies
        $isProRata = $setting && $user->doj && $user->doj->year === $year;

        // IDs of actively selected holidays
        $selectedHolidayIds = OptionalHolidaySelection::where('user_id', $user->id)
            ->where('year', $year)
            ->active()
            ->pluck('holiday_id')
            ->toArray();

        // IDs of cancelled selections (to show cancelled badge)
        $cancelledHolidayIds = OptionalHolidaySelection::where('user_id', $user->id)
            ->where('year', $year)
            ->cancelled()
            ->pluck('holiday_id')
            ->toArray();

        // All active optional holidays for this year, dept-scoped
        $holidays = Holiday::active()
            ->where('type', 'optional')
            ->whereYear('date', $year)
            ->where(function ($q) use ($user) {
                $q->where('scope', 'all')
                  ->orWhere(function ($q2) use ($user) {
                      $q2->where('scope', 'department')
                         ->where('department_id', $user->department_id);
                  });
            })
            ->orderBy('date')
            ->get()
            ->map(function ($h) use ($selectedHolidayIds, $cancelledHolidayIds) {
                $h->is_selected  = in_array($h->id, $selectedHolidayIds);
                $h->is_cancelled = in_array($h->id, $cancelledHolidayIds);
                // Today-or-past: cannot select or cancel
                $h->is_past      = $h->date->toDateString() <= now()->toDateString();
                return $h;
            });

        return view('optional-holidays.index', compact(
            'holidays',
            'setting',
            'eligibleCount',
            'maxAllowed',
            'usedCount',
            'remaining',
            'isProRata',
            'year'
        ));
    }

    public function select(Request $request)
    {
        $request->validate([
            'holiday_id' => ['required', 'integer', 'exists:holidays,id'],
        ]);

        $user      = auth()->user();
        $year      = now()->year;
        $holidayId = (int) $request->holiday_id;

        $holiday = Holiday::findOrFail($holidayId);

        if ($holiday->type !== 'optional') {
            return back()->with('error', 'Only optional holidays can be selected.');
        }

        if (! $holiday->status) {
            return back()->with('error', 'This holiday is not active.');
        }

        if ($holiday->date->year !== $year) {
            return back()->with('error', 'This holiday does not belong to the current year.');
        }

        // Cannot select today or a past holiday
        if ($holiday->date->toDateString() <= now()->toDateString()) {
            return back()->with('error', 'Cannot select a holiday that is today or in the past.');
        }

        if ($holiday->scope === 'department' && $holiday->department_id !== $user->department_id) {
            return back()->with('error', 'This holiday is not available for your department.');
        }

        $setting = OptionalHolidaySetting::forYear($year);
        if (! $setting) {
            return back()->with('error', 'No optional holiday setting is configured for this year.');
        }

        // Pro-rata eligible count for this employee
        $eligibleCount = OptionalHolidaySetting::getEligibleCount($user, $year, $setting);

        // Check limit (only active selections count)
        $usedCount = OptionalHolidaySelection::where('user_id', $user->id)
            ->where('year', $year)
            ->active()
            ->count();

        if ($usedCount >= $eligibleCount) {
            return back()->with('error', "You have already used all {$eligibleCount} optional holiday(s) you are eligible for in {$year}.");
        }

        // Check for an existing ACTIVE selection (duplicates not allowed)
        $alreadySelected = OptionalHolidaySelection::where('user_id', $user->id)
            ->where('holiday_id', $holidayId)
            ->active()
            ->exists();

        if ($alreadySelected) {
            return back()->with('error', 'You have already selected this holiday.');
        }

        // If a cancelled record exists for same holiday, reactivate it
        $cancelled = OptionalHolidaySelection::where('user_id', $user->id)
            ->where('holiday_id', $holidayId)
            ->cancelled()
            ->first();

        if ($cancelled) {
            $old = $cancelled->only(['status', 'cancelled_by', 'cancelled_at']);
            $cancelled->update([
                'status'       => 'active',
                'selected_at'  => now(),
                'cancelled_by' => null,
                'cancelled_at' => null,
            ]);
            $selection = $cancelled;
        } else {
            $selection = OptionalHolidaySelection::create([
                'user_id'     => $user->id,
                'holiday_id'  => $holidayId,
                'year'        => $year,
                'selected_at' => now(),
                'status'      => 'active',
            ]);
            $old = null;
        }

        // Mark attendance as optional_holiday for that date
        Attendance::updateOrCreate(
            ['user_id' => $user->id, 'date' => $holiday->date->toDateString()],
            ['status'  => 'optional_holiday', 'note' => 'Optional Holiday: ' . $holiday->name]
        );

        // Audit log
        AuditLog::record(
            module:   'Optional Holiday',
            action:   'create',
            recordId: $selection->id,
            userId:   $user->id,
            oldValue: $old,
            newValue: ['holiday_id' => $holidayId, 'holiday_name' => $holiday->name, 'date' => $holiday->date->toDateString(), 'status' => 'active'],
        );

        return back()->with('success', "\"{$holiday->name}\" selected as your optional holiday.");
    }

    public function deselect(Request $request)
    {
        $request->validate([
            'holiday_id' => ['required', 'integer', 'exists:holidays,id'],
        ]);

        $user      = auth()->user();
        $year      = now()->year;
        $holidayId = (int) $request->holiday_id;

        $holiday = Holiday::findOrFail($holidayId);

        // Cannot cancel today or a past holiday
        if ($holiday->date->toDateString() <= now()->toDateString()) {
            return back()->with('error', 'Cannot cancel a holiday that is today or in the past.');
        }

        $selection = OptionalHolidaySelection::where('user_id', $user->id)
            ->where('holiday_id', $holidayId)
            ->where('year', $year)
            ->active()
            ->first();

        if (! $selection) {
            return back()->with('error', 'This holiday was not selected or is already cancelled.');
        }

        $old = $selection->only(['status', 'cancelled_by', 'cancelled_at', 'selected_at']);

        // Soft-cancel: never delete
        $selection->update([
            'status'       => 'cancelled',
            'cancelled_by' => $user->id,
            'cancelled_at' => now(),
        ]);

        // Revert attendance record
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $holiday->date->toDateString())
            ->where('status', 'optional_holiday')
            ->first();

        if ($attendance) {
            if ($attendance->punchLogs()->count() > 0) {
                $attendance->update(['status' => 'present', 'note' => null]);
            } else {
                $attendance->delete();
            }
        }

        // Audit log
        AuditLog::record(
            module:   'Optional Holiday',
            action:   'cancel',
            recordId: $selection->id,
            userId:   $user->id,
            oldValue: $old,
            newValue: ['status' => 'cancelled', 'cancelled_by' => $user->id, 'cancelled_at' => now()->toDateTimeString()],
        );

        return back()->with('success', "\"{$holiday->name}\" removed from your optional holidays.");
    }
}
