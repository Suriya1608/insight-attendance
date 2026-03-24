<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('employees')->orderBy('name')->get();

        $stats = [
            'total'          => $departments->count(),
            'active'         => $departments->where('status', 'active')->count(),
            'with_saturday'  => $departments->where('saturday_rule', '!=', 'none')->count(),
            'total_assigned' => $departments->sum('employees_count'),
        ];

        return view('admin.departments.index', compact('departments', 'stats'));
    }

    public function create()
    {
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'               => ['required', 'string', 'max:150', 'unique:departments,name'],
            'code'               => ['nullable', 'string', 'max:10'],
            'description'        => ['nullable', 'string', 'max:255'],
            'saturday_rule'      => ['required', 'in:none,2nd_saturday_off,all_saturdays_off,alternating_saturdays'],
            'status'             => ['required', 'in:active,inactive'],
            'has_saturday_leave' => ['nullable', 'boolean'],
        ]);

        Department::create($request->only('name', 'code', 'description', 'saturday_rule', 'status', 'has_saturday_leave'));

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department created successfully.');
    }

    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name'               => ['required', 'string', 'max:150', 'unique:departments,name,' . $department->id],
            'code'               => ['nullable', 'string', 'max:10'],
            'description'        => ['nullable', 'string', 'max:255'],
            'saturday_rule'      => ['required', 'in:none,2nd_saturday_off,all_saturdays_off,alternating_saturdays'],
            'status'             => ['required', 'in:active,inactive'],
            'has_saturday_leave' => ['nullable', 'boolean'],
        ]);

        $department->update($request->only('name', 'code', 'description', 'saturday_rule', 'status', 'has_saturday_leave'));

        return redirect()->route('admin.departments.index')
            ->with('success', 'Department updated successfully.');
    }

    public function toggleStatus(Department $department)
    {
        $department->update([
            'status' => $department->isActive() ? 'inactive' : 'active',
        ]);

        return back()->with('success', 'Status updated for "' . $department->name . '".');
    }

    public function destroy(Department $department)
    {
        $department->delete();

        return back()->with('success', '"' . $department->name . '" department deleted.');
    }
}
