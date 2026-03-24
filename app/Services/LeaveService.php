<?php

namespace App\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class LeaveService
{
    /**
     * Credit the current month's CL, permission, and saturday_leave to a user.
     * Uses department leave rules. Idempotent — safe to call multiple times.
     */
    public static function creditMonthForUser(User $user, Carbon $now): void
    {
        $dept = $user->relationLoaded('department') ? $user->department : $user->load('department')->department;

        $clAmt      = $dept ? (float) ($dept->cl_per_month         ?? 1) : 1.0;
        $permCount  = $dept ? (int)   ($dept->permissions_per_month ?? 2) : 2;
        $permHrs    = $dept ? (int)   ($dept->hours_per_permission  ?? 2) : 2;
        $hasSat     = $dept ? $dept->hasSaturdayLeave() : false;
        $ruleActive = $dept ? ($dept->leave_rule_active ?? true)    : true;

        if (! $ruleActive) {
            return;
        }

        $year  = (int) $now->year;
        $month = (int) $now->month;

        DB::transaction(function () use ($user, $year, $month, $now, $clAmt, $permCount, $permHrs, $hasSat) {
            self::creditCL($user->id, $year, $month, $now, $clAmt);
            self::creditPermission($user->id, $year, $month, $now, $permCount, $permHrs);

            if ($hasSat) {
                self::creditSaturdayLeave($user->id, $year, $month, $now);
            }
        });
    }

    public static function creditCL(int $userId, int $year, int $month, Carbon $now, float $amount): void
    {
        if (LeaveTransaction::alreadyCredited($userId, 'CL', $year, $month)) {
            return;
        }

        $balance = LeaveBalance::clForYear($userId, $year);
        $balance->increment('credited', $amount);

        LeaveTransaction::create([
            'user_id'          => $userId,
            'leave_type'       => 'CL',
            'transaction_type' => 'credit',
            'amount'           => $amount,
            'year'             => $year,
            'month'            => $month,
            'date'             => $now->toDateString(),
            'remarks'          => "Monthly CL credit — {$now->format('F Y')}",
        ]);
    }

    public static function creditPermission(int $userId, int $year, int $month, Carbon $now, int $count, int $hrs): void
    {
        if (LeaveTransaction::alreadyCredited($userId, 'permission', $year, $month)) {
            return;
        }

        LeaveBalance::updateOrCreate(
            ['user_id' => $userId, 'leave_type' => 'permission', 'year' => $year, 'month' => $month],
            ['credited' => $count, 'used' => 0, 'lapsed' => 0]
        );

        LeaveTransaction::create([
            'user_id'          => $userId,
            'leave_type'       => 'permission',
            'transaction_type' => 'credit',
            'amount'           => $count,
            'year'             => $year,
            'month'            => $month,
            'date'             => $now->toDateString(),
            'remarks'          => "Monthly permission credit — {$now->format('F Y')} ({$count} permissions × up to {$hrs} hrs each)",
        ]);
    }

    public static function creditSaturdayLeave(int $userId, int $year, int $month, Carbon $now): void
    {
        if (LeaveTransaction::alreadyCredited($userId, 'saturday_leave', $year, $month)) {
            return;
        }

        LeaveBalance::updateOrCreate(
            ['user_id' => $userId, 'leave_type' => 'saturday_leave', 'year' => $year, 'month' => $month],
            ['credited' => 1, 'used' => 0, 'lapsed' => 0]
        );

        LeaveTransaction::create([
            'user_id'          => $userId,
            'leave_type'       => 'saturday_leave',
            'transaction_type' => 'credit',
            'amount'           => 1,
            'year'             => $year,
            'month'            => $month,
            'date'             => $now->toDateString(),
            'remarks'          => "Monthly Saturday leave credit — {$now->format('F Y')}",
        ]);
    }
}
