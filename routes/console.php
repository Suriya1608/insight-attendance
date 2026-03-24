<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Leave & Permission Automation
|--------------------------------------------------------------------------
|
| leave:credit-monthly  — Runs on the 1st of every month at 00:00.
|   • Expires previous month's unused permissions and saturday_leaves.
|   • Credits 1 CL, 2 permissions (and 1 saturday_leave for HR Recruiting)
|     to every active employee/manager.
|
| leave:year-end-reset  — Runs on Dec 31st at 00:00.
|   • Lapses all remaining CL for the ending year.
|
*/
Schedule::command('leave:credit-monthly')->monthlyOn(1, '00:00');
Schedule::command('leave:year-end-reset')->yearlyOn(12, 31, '00:00');

/*
|--------------------------------------------------------------------------
| Attendance Automation
|--------------------------------------------------------------------------
|
| attendance:mark-missed-punchout  — Runs daily at 00:30.
|   • Finds attendance records with punch-in but no punch-out from previous
|     days and marks them as 'missed_punch_out'.
|   • Skips records already in 'missed_punch_out' or 'pending_regularization'.
|
*/
Schedule::command('attendance:mark-missed-punchout')->dailyAt('00:30');
