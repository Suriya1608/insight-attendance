<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LeaveController extends Controller
{
    public function index()
    {
        $now   = Carbon::now();
        $year  = $now->year;
        $month = $now->month;

        $employees = User::with('department')
            ->whereIn('role', ['employee', 'manager'])
            ->where('emp_status', 'active')
            ->orderBy('name')
            ->get();

        // Load all relevant balances in bulk
        $userIds = $employees->pluck('id')->all();

        $clBalances = LeaveBalance::where('leave_type', 'CL')
            ->where('year', $year)
            ->whereNull('month')
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        $permBalances = LeaveBalance::where('leave_type', 'permission')
            ->where('year', $year)
            ->where('month', $month)
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        $satBalances = LeaveBalance::where('leave_type', 'saturday_leave')
            ->where('year', $year)
            ->where('month', $month)
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        return view('admin.leave.index', compact(
            'employees', 'clBalances', 'permBalances', 'satBalances', 'year', 'month'
        ));
    }

    public function show(User $employee)
    {
        $now   = Carbon::now();
        $year  = $now->year;
        $month = $now->month;

        // Current balances
        $cl = LeaveBalance::where('user_id', $employee->id)
            ->where('leave_type', 'CL')->where('year', $year)->whereNull('month')
            ->first();

        $perm = LeaveBalance::where('user_id', $employee->id)
            ->where('leave_type', 'permission')->where('year', $year)->where('month', $month)
            ->first();

        $sat = LeaveBalance::where('user_id', $employee->id)
            ->where('leave_type', 'saturday_leave')->where('year', $year)->where('month', $month)
            ->first();

        // Full transaction history (latest first)
        $transactions = LeaveTransaction::where('user_id', $employee->id)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.leave.show', compact(
            'employee', 'cl', 'perm', 'sat', 'transactions', 'year', 'month'
        ));
    }

    public function adjust(Request $request, User $employee)
    {
        $request->validate([
            'leave_type'       => ['required', 'in:CL,permission,saturday_leave'],
            'transaction_type' => ['required', 'in:credit,debit,lapse'],
            'amount'           => ['required', 'numeric', 'min:0.5', 'max:999'],
            'remarks'          => ['required', 'string', 'max:255'],
        ]);

        $now   = Carbon::now();
        $year  = $now->year;
        $month = $now->month;

        DB::transaction(function () use ($request, $employee, $year, $month, $now) {
            $type  = $request->leave_type;
            $txType = $request->transaction_type;
            $amount = (float) $request->amount;

            // Fetch or create the balance row
            if ($type === 'CL') {
                $balance = LeaveBalance::clForYear($employee->id, $year);
            } else {
                $balance = LeaveBalance::monthlyFor($employee->id, $type, $year, $month);
            }

            // Apply to balance
            if ($txType === 'credit') {
                $balance->increment('credited', $amount);
            } elseif ($txType === 'debit') {
                $balance->increment('used', $amount);
            } else { // lapse
                $balance->increment('lapsed', $amount);
            }

            // Record transaction
            LeaveTransaction::create([
                'user_id'          => $employee->id,
                'leave_type'       => $type,
                'transaction_type' => $txType,
                'amount'           => $amount,
                'year'             => $year,
                'month'            => $month,
                'date'             => $now->toDateString(),
                'remarks'          => '[Admin] ' . $request->remarks,
            ]);
        });

        return back()->with('success', 'Leave balance adjusted successfully.');
    }
}
