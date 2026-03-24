<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class LeaveRuleController extends Controller
{
    public function index()
    {
        $departments = Department::withCount('employees')->orderBy('name')->get();

        $stats = [
            'total'            => $departments->count(),
            'flexible_sat'     => $departments->whereIn('saturday_rule', ['flexible_saturday', 'carry_forward'])->count(),
            'fixed_sat_off'    => $departments->whereIn('saturday_rule', ['2nd_saturday_off', '4th_saturday_off', 'all_saturdays_off'])->count(),
            'rules_configured' => $departments->where('leave_rule_active', true)->count(),
        ];

        return view('admin.leave-rules.index', compact('departments', 'stats'));
    }

    public function edit(Department $leaveRule)
    {
        return view('admin.leave-rules.edit', ['department' => $leaveRule]);
    }

    public function update(Request $request, Department $leaveRule)
    {
        $request->validate([
            'cl_per_month'          => ['required', 'numeric', 'min:0', 'max:30'],
            'permissions_per_month' => ['required', 'integer', 'min:0', 'max:10'],
            'hours_per_permission'  => ['required', 'integer', 'min:1', 'max:8'],
            'saturday_rule'         => ['required', 'in:none,2nd_saturday_off,4th_saturday_off,flexible_saturday,carry_forward,all_saturdays_off'],
            'leave_rule_active'     => ['nullable', 'boolean'],
            'leave_rule_notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        $saturdayRule = $request->saturday_rule;

        $leaveRule->update([
            'cl_per_month'          => $request->cl_per_month,
            'permissions_per_month' => $request->permissions_per_month,
            'hours_per_permission'  => $request->hours_per_permission,
            'saturday_rule'         => $saturdayRule,
            // keep has_saturday_leave in sync for the credit command
            'has_saturday_leave'    => in_array($saturdayRule, ['flexible_saturday', 'carry_forward']),
            'leave_rule_active'     => $request->boolean('leave_rule_active'),
            'leave_rule_notes'      => $request->leave_rule_notes,
        ]);

        return redirect()->route('admin.leave-rules.index')
            ->with('success', 'Leave rules updated for "' . $leaveRule->name . '".');
    }
}
