<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\AuditLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class MarkMissedPunchOut extends Command
{
    protected $signature   = 'attendance:mark-missed-punchout {--dry-run : Show what would be updated without writing}';
    protected $description = 'Mark past attendance records with punch-in but no punch-out as missed_punch_out.';

    public function handle(): int
    {
        $dry   = $this->option('dry-run');
        $today = Carbon::today()->toDateString();

        $query = Attendance::whereNotNull('punch_in')
            ->whereNull('punch_out')
            ->whereDate('date', '<', $today)
            ->whereNotIn('status', ['missed_punch_out', 'pending_regularization']);

        $count = $query->count();

        if ($count === 0) {
            $this->info('No open attendance records found to mark.');
            return self::SUCCESS;
        }

        $this->info($dry ? "[DRY-RUN] " : "" . "Found {$count} record(s) to mark as missed_punch_out.");

        if ($dry) {
            $query->each(function (Attendance $att) {
                $this->line("  Would mark: user_id={$att->user_id} date={$att->date->toDateString()} current_status={$att->status}");
            });
            return self::SUCCESS;
        }

        $query->each(function (Attendance $att) {
            $oldStatus = $att->status;
            $marker    = 'Auto-marked missed_punch_out by daily cron.';
            $note      = trim((string) $att->note);

            $att->update([
                'status' => 'missed_punch_out',
                'note'   => str_contains($note, $marker) ? $note : trim($note . ' ' . $marker),
            ]);

            AuditLog::record('attendance', 'marked_missed_punch_out', $att->id, null, ['status' => $oldStatus], [
                'status' => 'missed_punch_out',
                'note'   => $att->note,
            ]);
        });

        $this->info("Marked {$count} attendance record(s) as missed_punch_out.");
        Log::info("attendance:mark-missed-punchout marked {$count} records.", ['date' => $today]);

        return self::SUCCESS;
    }
}
