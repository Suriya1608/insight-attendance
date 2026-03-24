<?php

namespace Database\Seeders;

use App\Models\CronJob;
use Illuminate\Database\Seeder;

class CronJobSeeder extends Seeder
{
    public function run(): void
    {
        $jobs = [
            [
                'key'              => 'attendance-mark-missed-punchout',
                'name'             => 'Mark Missed Punch-Out',
                'command'          => 'attendance:mark-missed-punchout',
                'description'      => 'Finds attendance records from past dates where employee punched in but never punched out, and marks them as "missed_punch_out". Runs daily at 00:30.',
                'schedule_display' => 'Daily at 00:30',
                'is_active'        => true,
            ],
            [
                'key'              => 'leave-credit-monthly',
                'name'             => 'Monthly Leave Credit',
                'command'          => 'leave:credit-monthly',
                'description'      => 'Credits monthly CL, permissions, and Saturday leave to all active employees/managers based on department rules. Also expires the previous month\'s unused permissions and Saturday leaves.',
                'schedule_display' => '1st of every month at 00:00',
                'is_active'        => true,
            ],
            [
                'key'              => 'leave-year-end-reset',
                'name'             => 'Year-End CL Reset',
                'command'          => 'leave:year-end-reset',
                'description'      => 'Lapses all remaining Casual Leave (CL) balances at year-end (December 31st). CL does not carry forward to the next year.',
                'schedule_display' => 'December 31st at 00:00',
                'is_active'        => true,
            ],
        ];

        foreach ($jobs as $job) {
            CronJob::updateOrCreate(['key' => $job['key']], $job);
        }
    }
}
