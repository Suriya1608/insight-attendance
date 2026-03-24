<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use App\Services\LeaveService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CreditMonthlyLeaves extends Command
{
    protected $signature   = 'leave:credit-monthly {--dry-run : Show what would be credited without writing}';
    protected $description = 'Credit monthly CL, permissions, and saturday leave based on department leave rules. Also expires previous-month permissions/saturday_leaves.';

    public function handle(): int
    {
        $now       = Carbon::now();
        $year      = (int) $now->year;
        $month     = (int) $now->month;
        $prev      = $now->copy()->subMonthNoOverflow();
        $prevYear  = (int) $prev->year;
        $prevMonth = (int) $prev->month;
        $dry       = $this->option('dry-run');

        $this->info($dry ? '[DRY-RUN] ' : '' . "Running monthly leave credit for {$now->format('F Y')} …");

        // ── Step 1: Expire last month's permission & saturday_leave balances ──────
        $this->expireMonthly($prevYear, $prevMonth, $dry);

        // ── Step 2: Credit new leaves to all active employees / managers ──────────
        $users = User::whereIn('role', ['employee', 'manager'])
            ->where('emp_status', 'active')
            ->with('department')
            ->get();

        $credited = 0;
        foreach ($users as $user) {
            $dept       = $user->department;
            $clAmt      = $dept ? (float) ($dept->cl_per_month ?? 1) : 1.0;
            $permCount  = $dept ? (int)   ($dept->permissions_per_month ?? 2) : 2;
            $permHrs    = $dept ? (int)   ($dept->hours_per_permission ?? 2) : 2;
            $hasSat     = $dept ? $dept->hasSaturdayLeave() : false;
            $ruleActive = $dept ? ($dept->leave_rule_active ?? true) : true;

            if ($dry) {
                $this->line("  [DRY] Would credit {$clAmt} CL + {$permCount} permissions"
                    . ($hasSat ? ' + saturday_leave' : '')
                    . " to {$user->name} (#{$user->id})"
                    . ($ruleActive ? '' : ' [RULE INACTIVE — skip]'));
                $credited++;
                continue;
            }

            if (! $ruleActive) {
                continue;
            }

            LeaveService::creditMonthForUser($user, $now);

            $credited++;
        }

        $this->info($dry
            ? "[DRY-RUN] Would credit leaves to {$credited} employees."
            : "Monthly leave credit complete — {$credited} employees processed.");

        Log::info("leave:credit-monthly — {$credited} employees | {$now->format('F Y')}" . ($dry ? ' (dry-run)' : ''));

        return Command::SUCCESS;
    }

    // ── Private helpers ───────────────────────────────────────────────────────────

    private function expireMonthly(int $year, int $month, bool $dry): void
    {
        foreach (['permission', 'saturday_leave'] as $type) {
            $balances = LeaveBalance::where('leave_type', $type)
                ->where('year', $year)
                ->where('month', $month)
                ->get();

            foreach ($balances as $balance) {
                $remaining = max(0.0, $balance->credited - $balance->used - $balance->lapsed);
                if ($remaining <= 0) {
                    continue;
                }

                $label   = $type === 'permission' ? 'Permission' : 'Saturday leave';
                $endDate = Carbon::create($year, $month)->endOfMonth()->toDateString();

                if ($dry) {
                    $this->line("  [DRY] Would lapse {$remaining} {$label} for user #{$balance->user_id} ({$year}-{$month})");
                    continue;
                }

                $balance->increment('lapsed', $remaining);

                LeaveTransaction::create([
                    'user_id'          => $balance->user_id,
                    'leave_type'       => $type,
                    'transaction_type' => 'lapse',
                    'amount'           => $remaining,
                    'year'             => $year,
                    'month'            => $month,
                    'date'             => $endDate,
                    'remarks'          => "{$label} lapse — end of " . Carbon::create($year, $month)->format('F Y'),
                ]);
            }
        }
    }
}
