<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\IdCrypt;
use App\Http\Controllers\Controller;
use App\Mail\EmployeeCredentialsMail;
use App\Models\Department;
use App\Models\EmployeeDetail;
use App\Models\User;
use App\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search'     => ['nullable', 'string', 'max:100'],
            'department' => ['nullable', 'string', 'max:100'],
            'role'       => ['nullable', 'in:employee,manager'],
            'status'     => ['nullable', 'in:active,inactive'],
        ]);

        $query = User::with(['department', 'employeeDetail'])
                     ->whereIn('role', ['employee', 'manager'])
                     ->orderBy('name');

        // Search — escape LIKE wildcards to prevent wildcard injection
        if ($search = $request->input('search')) {
            $safe = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $search);
            $query->where(function ($q) use ($safe) {
                $q->where('name', 'like', "%{$safe}%")
                  ->orWhere('email', 'like', "%{$safe}%")
                  ->orWhere('employee_code', 'like', "%{$safe}%")
                  ->orWhere('mobile', 'like', "%{$safe}%");
            });
        }

        // Filter by department (encrypted ID in URL)
        if ($encDeptId = $request->input('department')) {
            $deptId = IdCrypt::decode($encDeptId);
            if ($deptId) {
                $query->where('department_id', $deptId);
            }
        }

        // Filter by role
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('emp_status', $status);
        }

        $employees   = $query->paginate(15)->withQueryString();
        $departments = Department::active()->orderBy('name')->get();

        $now = now();
        $stats = [
            'total'      => User::whereIn('role', ['employee', 'manager'])->count(),
            'managers'   => User::where('role', 'manager')->count(),
            'active'     => User::whereIn('role', ['employee', 'manager'])->where('emp_status', 'active')->count(),
            'departments'=> Department::active()->count(),
            'new_month'  => User::whereIn('role', ['employee', 'manager'])
                                ->whereYear('created_at', $now->year)
                                ->whereMonth('created_at', $now->month)
                                ->count(),
        ];

        return view('admin.employees.index', compact('employees', 'departments', 'stats'));
    }

    public function create()
    {
        $departments = Department::active()->orderBy('name')->get();
        $managers    = User::where('role', 'manager')->where('emp_status', 'active')->orderBy('name')->get();

        return view('admin.employees.create', compact('departments', 'managers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'               => ['required', 'string', 'max:100'],
            'email'              => ['required', 'email', 'unique:users,email'],
            'username'           => ['nullable', 'string', 'max:50', 'unique:users,username', 'regex:/^[a-zA-Z0-9._-]+$/'],
            'mobile'             => ['nullable', 'digits:10'],
            'dob'                => ['nullable', 'date', 'before:today'],
            'doj'                => ['nullable', 'date'],
            'role'               => ['required', 'in:employee,manager'],
            'department_id'      => ['nullable', 'exists:departments,id'],
            'emp_status'         => ['required', 'in:active,inactive'],
            'level1_manager_id'  => ['nullable', 'exists:users,id', 'different:level2_manager_id'],
            'level2_manager_id'  => ['nullable', 'exists:users,id', 'different:level1_manager_id'],
            // Identity
            'aadhaar_number'     => ['nullable', 'digits:12'],
            'pan_number'         => ['nullable', 'string', 'max:10'],
            // Bank
            'bank_name'          => ['nullable', 'string', 'max:100'],
            'bank_account_number'=> ['nullable', 'digits_between:8,20'],
            'ifsc_code'          => ['nullable', 'string', 'max:11'],
            // Emergency
            'emergency_contact'  => ['nullable', 'digits:10'],
            // Address
            'address_line1'      => ['nullable', 'string', 'max:255'],
            'address_line2'      => ['nullable', 'string', 'max:255'],
            'city'               => ['nullable', 'string', 'max:100'],
            'state'              => ['nullable', 'string', 'max:100'],
            'country'            => ['nullable', 'string', 'max:100'],
            // Compensation
            'salary'             => ['required', 'numeric', 'min:0'],
            // Additional
            'blood_group'        => ['nullable', 'string', 'max:5'],
            'father_name'        => ['nullable', 'string', 'max:100'],
            'mother_name'        => ['nullable', 'string', 'max:100'],
            'profile_image'      => ['nullable', 'image', 'max:2048'],
        ]);

        // Role-specific manager validation
        if ($request->role === 'employee') {
            $request->validate([
                'level1_manager_id' => ['required', 'exists:users,id'],
                'level2_manager_id' => ['required', 'exists:users,id'],
            ]);
        } elseif ($request->role === 'manager') {
            $request->validate([
                'level1_manager_id' => ['required', 'exists:users,id'],
            ]);
        }

        $plainPassword = 'Password@123';

        $user = User::create([
            'name'              => $request->name,
            'email'             => $request->email,
            'username'          => $request->username ?: null,
            'password'          => Hash::make($plainPassword),
            'role'              => $request->role,
            'department_id'     => $request->department_id,
            'employee_code'     => User::generateEmployeeCode(),
            'mobile'            => $request->mobile ? '+91' . $request->mobile : null,
            'emp_status'        => $request->emp_status,
            'dob'               => $request->dob,
            'doj'               => $request->doj,
            'level1_manager_id' => $request->level1_manager_id,
            'level2_manager_id' => $request->role === 'employee' ? $request->level2_manager_id : null,
            'salary'            => $request->salary,
        ]);

        // Handle profile image
        $profileImage = null;
        if ($request->hasFile('profile_image')) {
            $profileImage = $request->file('profile_image')->store('employees/photos', 'public');
        }

        EmployeeDetail::create([
            'user_id'            => $user->id,
            'aadhaar_number'     => $request->aadhaar_number,
            'pan_number'         => $request->pan_number ? strtoupper($request->pan_number) : null,
            'bank_name'          => $request->bank_name,
            'bank_account_number'=> $request->bank_account_number,
            'ifsc_code'          => $request->ifsc_code ? strtoupper($request->ifsc_code) : null,
            'emergency_contact'  => $request->emergency_contact ? '+91' . $request->emergency_contact : null,
            'address_line1'      => $request->address_line1,
            'address_line2'      => $request->address_line2,
            'city'               => $request->city,
            'state'              => $request->state,
            'country'            => $request->country ?? 'India',
            'blood_group'        => $request->blood_group,
            'father_name'        => $request->father_name,
            'mother_name'        => $request->mother_name,
            'profile_image'      => $profileImage,
        ]);

        // Credit this month's leave & permission balances for the new employee
        $user->load('department');
        LeaveService::creditMonthForUser($user, Carbon::now());

        // Send credentials email
        try {
            Mail::to($user->email)->send(new EmployeeCredentialsMail($user, $plainPassword));
        } catch (\Exception $e) {
            // Log silently — don't fail the request if mail is not configured
            Log::warning("Could not send credentials email to {$user->email}: " . $e->getMessage());
        }

        return redirect()->route('admin.employees.index')
            ->with('success', "Employee {$user->name} ({$user->employee_code}) created. Login credentials sent to {$user->email}.");
    }

    public function show(User $employee)
    {
        $employee->load(['department', 'employeeDetail', 'level1Manager', 'level2Manager']);
        return view('admin.employees.show', compact('employee'));
    }

    public function edit(User $employee)
    {
        $employee->load('employeeDetail');
        $departments = Department::active()->orderBy('name')->get();
        $managers    = User::where('role', 'manager')
                           ->where('emp_status', 'active')
                           ->where('id', '!=', $employee->id)
                           ->orderBy('name')
                           ->get();

        return view('admin.employees.edit', compact('employee', 'departments', 'managers'));
    }

    public function update(Request $request, User $employee)
    {
        $request->validate([
            'name'               => ['required', 'string', 'max:100'],
            'email'              => ['required', 'email', 'unique:users,email,' . $employee->id],
            'username'           => ['nullable', 'string', 'max:50', 'unique:users,username,' . $employee->id, 'regex:/^[a-zA-Z0-9._-]+$/'],
            'mobile'             => ['nullable', 'digits:10'],
            'dob'                => ['nullable', 'date', 'before:today'],
            'doj'                => ['nullable', 'date'],
            'role'               => ['required', 'in:employee,manager'],
            'department_id'      => ['nullable', 'exists:departments,id'],
            'emp_status'         => ['required', 'in:active,inactive'],
            'level1_manager_id'  => ['nullable', 'exists:users,id', 'different:level2_manager_id'],
            'level2_manager_id'  => ['nullable', 'exists:users,id', 'different:level1_manager_id'],
            'aadhaar_number'     => ['nullable', 'digits:12'],
            'pan_number'         => ['nullable', 'string', 'max:10'],
            'bank_name'          => ['nullable', 'string', 'max:100'],
            'bank_account_number'=> ['nullable', 'digits_between:8,20'],
            'ifsc_code'          => ['nullable', 'string', 'max:11'],
            'emergency_contact'  => ['nullable', 'digits:10'],
            'address_line1'      => ['nullable', 'string', 'max:255'],
            'address_line2'      => ['nullable', 'string', 'max:255'],
            'city'               => ['nullable', 'string', 'max:100'],
            'state'              => ['nullable', 'string', 'max:100'],
            'country'            => ['nullable', 'string', 'max:100'],
            'blood_group'        => ['nullable', 'string', 'max:5'],
            'father_name'        => ['nullable', 'string', 'max:100'],
            'mother_name'        => ['nullable', 'string', 'max:100'],
            'profile_image'      => ['nullable', 'image', 'max:2048'],
            // Compensation
            'salary'             => ['required', 'numeric', 'min:0'],
        ]);

        if ($request->role === 'employee') {
            $request->validate([
                'level1_manager_id' => ['required', 'exists:users,id'],
                'level2_manager_id' => ['required', 'exists:users,id'],
            ]);
        } elseif ($request->role === 'manager') {
            $request->validate([
                'level1_manager_id' => ['required', 'exists:users,id'],
            ]);
        }

        $employee->update([
            'name'              => $request->name,
            'email'             => $request->email,
            'username'          => $request->username ?: null,
            'role'              => $request->role,
            'department_id'     => $request->department_id,
            'mobile'            => $request->mobile ? '+91' . $request->mobile : null,
            'emp_status'        => $request->emp_status,
            'dob'               => $request->dob,
            'doj'               => $request->doj,
            'level1_manager_id' => $request->level1_manager_id,
            'level2_manager_id' => $request->role === 'employee' ? $request->level2_manager_id : null,
            'salary'            => $request->salary,
        ]);

        // Handle profile image
        $detail = $employee->employeeDetail ?? new EmployeeDetail(['user_id' => $employee->id]);

        if ($request->hasFile('profile_image')) {
            if ($detail->profile_image) {
                Storage::disk('public')->delete($detail->profile_image);
            }
            $detail->profile_image = $request->file('profile_image')->store('employees/photos', 'public');
        }

        $detail->fill([
            'aadhaar_number'     => $request->aadhaar_number,
            'pan_number'         => $request->pan_number ? strtoupper($request->pan_number) : null,
            'bank_name'          => $request->bank_name,
            'bank_account_number'=> $request->bank_account_number,
            'ifsc_code'          => $request->ifsc_code ? strtoupper($request->ifsc_code) : null,
            'emergency_contact'  => $request->emergency_contact ? '+91' . $request->emergency_contact : null,
            'address_line1'      => $request->address_line1,
            'address_line2'      => $request->address_line2,
            'city'               => $request->city,
            'state'              => $request->state,
            'country'            => $request->country ?? 'India',
            'blood_group'        => $request->blood_group,
            'father_name'        => $request->father_name,
            'mother_name'        => $request->mother_name,
        ]);

        $detail->save();

        return redirect()->route('admin.employees.show', $employee)
            ->with('success', 'Employee updated successfully.');
    }

    public function checkUsername(Request $request)
    {
        $query = User::where('username', $request->input('username'));

        if ($encExcludeId = $request->input('exclude_id')) {
            $excludeId = IdCrypt::decode($encExcludeId);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
        }

        return response()->json(['available' => !$query->exists()]);
    }
}
