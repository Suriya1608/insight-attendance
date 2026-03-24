<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CronJob;
use App\Models\CronJobLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class CronJobController extends Controller
{
    public function index(Request $request)
    {
        $jobs = CronJob::orderBy('name')->get();

        // Paginated logs (latest first), optionally filtered by job
        $logsQuery = CronJobLog::with(['cronJob', 'triggeredBy'])
            ->latest('started_at');

        if ($request->filled('job')) {
            $logsQuery->where('cron_job_id', $request->job);
        }

        $logs = $logsQuery->paginate(15)->withQueryString();

        $stats = [
            'total'        => $jobs->count(),
            'active'       => $jobs->where('is_active', true)->count(),
            'last_success' => $jobs->where('last_run_status', 'success')->count(),
            'last_failed'  => $jobs->where('last_run_status', 'failed')->count(),
        ];

        return view('admin.cron-jobs.index', compact('jobs', 'logs', 'stats'));
    }

    public function run(CronJob $cronJob)
    {
        if (! $cronJob->is_active) {
            return back()->with('error', "Job \"{$cronJob->name}\" is inactive and cannot be run.");
        }

        $startedAt = now();
        $startMs   = (int) (microtime(true) * 1000);

        // Create a "running" log entry immediately
        $log = CronJobLog::create([
            'cron_job_id'  => $cronJob->id,
            'triggered_by' => Auth::id(),
            'trigger_type' => 'manual',
            'status'       => 'running',
            'started_at'   => $startedAt,
        ]);

        try {
            $exitCode = Artisan::call($cronJob->command);
            $output   = Artisan::output();
            $status   = ($exitCode === 0) ? 'success' : 'failed';
        } catch (\Throwable $e) {
            $output = "[Exception]\n" . $e->getMessage() . "\n\n" . $e->getTraceAsString();
            $status = 'failed';
        }

        $durationMs = (int) (microtime(true) * 1000) - $startMs;
        $finishedAt = now();

        // Update log entry
        $log->update([
            'status'      => $status,
            'output'      => $output,
            'duration_ms' => $durationMs,
            'finished_at' => $finishedAt,
        ]);

        // Sync last-run stats onto the job row
        $cronJob->update([
            'last_run_at'          => $startedAt,
            'last_run_status'      => $status,
            'last_run_duration_ms' => $durationMs,
        ]);

        $flash = $status === 'success'
            ? "Job \"{$cronJob->name}\" ran successfully ({$log->formattedDuration()})."
            : "Job \"{$cronJob->name}\" finished with errors. Check the log below.";

        return redirect()->route('admin.cron-jobs.index')
            ->with($status === 'success' ? 'success' : 'error', $flash);
    }

    public function showLog(CronJobLog $log)
    {
        $log->load(['cronJob', 'triggeredBy']);
        return view('admin.cron-jobs.log', compact('log'));
    }
}
