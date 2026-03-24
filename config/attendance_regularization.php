<?php

return [
    'types' => [
        'missed_punch_out' => 'Missed Punch-Out',
        'missed_punch_in' => 'Missed Punch-In',
        'time_correction' => 'Time Correction',
    ],

    'min_time' => env('ATTENDANCE_REGULARIZATION_MIN_TIME', '00:00'),
    'max_time' => env('ATTENDANCE_REGULARIZATION_MAX_TIME', '23:59'),
    'max_duration_minutes' => (int) env('ATTENDANCE_REGULARIZATION_MAX_DURATION_MINUTES', 18 * 60),
];
