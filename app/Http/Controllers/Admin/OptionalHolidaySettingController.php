<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Models\OptionalHolidaySetting;
use Illuminate\Http\Request;

class OptionalHolidaySettingController extends Controller
{
    public function index()
    {
        $settings = OptionalHolidaySetting::orderByDesc('year')->get();

        // Optional holidays defined so admin can see which years have holidays set
        $optionalHolidayYears = Holiday::where('type', 'optional')
            ->where('status', true)
            ->selectRaw('YEAR(date) as y')
            ->distinct()
            ->pluck('y');

        $currentYear = now()->year;

        return view('admin.optional-holiday-settings.index', compact(
            'settings',
            'optionalHolidayYears',
            'currentYear'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'year'        => ['required', 'integer', 'min:2020', 'max:2100', 'unique:optional_holiday_settings,year'],
            'max_allowed' => ['required', 'integer', 'min:1', 'max:30'],
            'description' => ['nullable', 'string', 'max:500'],
            'status'      => ['nullable', 'boolean'],
        ]);

        $data['status'] = $request->boolean('status', true);

        OptionalHolidaySetting::create($data);

        return redirect()->route('admin.optional-holiday-settings.index')
            ->with('success', "Optional holiday setting for {$data['year']} saved.");
    }

    public function update(Request $request, OptionalHolidaySetting $optionalHolidaySetting)
    {
        $data = $request->validate([
            'max_allowed' => ['required', 'integer', 'min:1', 'max:30'],
            'description' => ['nullable', 'string', 'max:500'],
            'status'      => ['nullable', 'boolean'],
        ]);

        $data['status'] = $request->boolean('status', true);

        $optionalHolidaySetting->update($data);

        return redirect()->route('admin.optional-holiday-settings.index')
            ->with('success', "Setting for {$optionalHolidaySetting->year} updated.");
    }

    public function destroy(OptionalHolidaySetting $optionalHolidaySetting)
    {
        $year = $optionalHolidaySetting->year;
        $optionalHolidaySetting->delete();

        return redirect()->route('admin.optional-holiday-settings.index')
            ->with('success', "Setting for {$year} deleted.");
    }
}
