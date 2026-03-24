<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\IdCrypt;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Holiday;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'year'   => ['nullable', 'integer', 'between:2000,2100'],
            'type'   => ['nullable', 'in:national,optional'],
        ]);

        $query = Holiday::with('department')->orderBy('date', 'asc');

        // Filters — escape LIKE wildcards to prevent wildcard injection
        if ($request->filled('search')) {
            $safe = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $request->search);
            $query->where('name', 'like', '%' . $safe . '%');
        }

        if ($request->filled('year')) {
            $query->whereYear('date', (int) $request->year);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('department') && $request->department !== 'all') {
            $deptId = IdCrypt::decode($request->department);
            if ($deptId) {
                $query->where(function ($q) use ($deptId) {
                    $q->where('scope', 'all')
                      ->orWhere(function ($q2) use ($deptId) {
                          $q2->where('scope', 'department')
                             ->where('department_id', $deptId);
                      });
                });
            }
        }

        $holidays    = $query->get();
        $departments = Department::orderBy('name')->get();

        $today = Carbon::today();

        $stats = [
            'total'       => Holiday::count(),
            'today'       => Holiday::whereDate('date', $today)->where('status', true)->count(),
            'next30'      => Holiday::whereBetween('date', [$today->copy()->addDay(), $today->copy()->addDays(30)])
                                    ->where('status', true)->count(),
            'departments' => $departments->count(),
        ];

        $years = Holiday::selectRaw('YEAR(date) as y')->distinct()->orderByDesc('y')->pluck('y');
        if ($years->isEmpty()) {
            $years = collect([now()->year]);
        }

        return view('admin.holidays.index', compact('holidays', 'departments', 'stats', 'years'));
    }

    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.holidays.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'date'          => ['required', 'date'],
            'type'          => ['required', 'in:national,optional'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'status'        => ['nullable', 'boolean'],
            'scope'         => ['required', 'in:all,department'],
            'department_id' => ['nullable', 'exists:departments,id', 'required_if:scope,department'],
        ]);

        // Duplicate date check
        $this->checkDuplicate($data['date'], $data['scope'], $data['department_id'] ?? null);

        $data['status']        = $request->boolean('status', true);
        $data['department_id'] = $data['scope'] === 'all' ? null : ($data['department_id'] ?? null);

        Holiday::create($data);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday "' . $data['name'] . '" added successfully.');
    }

    public function edit(Holiday $holiday)
    {
        $departments = Department::orderBy('name')->get();
        return view('admin.holidays.edit', compact('holiday', 'departments'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'date'          => ['required', 'date'],
            'type'          => ['required', 'in:national,optional'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'status'        => ['nullable', 'boolean'],
            'scope'         => ['required', 'in:all,department'],
            'department_id' => ['nullable', 'exists:departments,id', 'required_if:scope,department'],
        ]);

        // Duplicate date check (exclude current record)
        $this->checkDuplicate($data['date'], $data['scope'], $data['department_id'] ?? null, $holiday->id);

        $data['status']        = $request->boolean('status', true);
        $data['department_id'] = $data['scope'] === 'all' ? null : ($data['department_id'] ?? null);

        $holiday->update($data);

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday "' . $data['name'] . '" updated successfully.');
    }

    public function destroy(Holiday $holiday)
    {
        $name = $holiday->name;
        $holiday->delete();

        return redirect()->route('admin.holidays.index')
            ->with('success', 'Holiday "' . $name . '" deleted.');
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private function checkDuplicate(string $date, string $scope, ?int $deptId, ?int $excludeId = null): void
    {
        $query = Holiday::whereDate('date', $date);

        if ($scope === 'all') {
            $query->where('scope', 'all');
        } else {
            $query->where('scope', 'department')->where('department_id', $deptId);
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            $label = $scope === 'all' ? 'for all departments' : 'for this department';
            abort(422, "A holiday already exists on this date {$label}.");
        }
    }
}
