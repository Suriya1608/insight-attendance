<?php

namespace App\Console\Commands;

use App\Models\LeaveBalance;
use App\Models\LeaveTransaction;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class YearEndLeaveReset extends Command
{
    protected $signature   = 'leave:year-end-reset {--dry-run : Show what would be lapsed without writing}';
    protected $description = 'Expire all remaining CL balances on December 31st (year-end reset).';

    public function handle(): int
    {
        $now  = Carbon::now();
        $year = (int) $now->year;
        $dry  = $this->option('dry-run');

        $this->info($dry ? '[DRY-RUN] ' : '' . "Running year-end CL reset for {$year} …");

        $balances = LeaveBalance::where('leave_type', 'CL')
            ->where('year', $year)
            ->get();

        $lapsed = 0;
        foreach ($balances as $balance) {
            $remaining = max(0.0, $balance->credited - $balance->used - $balance->lapsed);
            if ($remaining <= 0) {
                continue;
            }

            if ($dry) {
                $this->line("  [DRY] Would lapse {$remaining} CL for user #{$balance->user_id}");
                $lapsed++;
                continue;
            }

            $balance->increment('lapsed', $remaining);

            LeaveTransaction::create([
                'user_id'          => $balance->user_id,
                'leave_type'       => 'CL',
                'transaction_type' => 'lapse',
                'amount'           => $remaining,
                'year'             => $year,
                'month'            => 12,
                'date'             => $now->toDateString(),
                'remarks'          => "Year-end CL lapse — Dec 31, {$year}",
            ]);

            $lapsed++;
        }

        $this->info($dry
            ? "[DRY-RUN] Would lapse CL for {$lapsed} employees."
            : "Year-end CL reset complete — {$lapsed} balances lapsed for {$year}.");

        Log::info("leave:year-end-reset — {$lapsed} CL balances lapsed for {$year}" . ($dry ? ' (dry-run)' : ''));

        return Command::SUCCESS;
    }
}
