<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Storage Disk
    |--------------------------------------------------------------------------
    | Uses the "local" (private) disk so files are never publicly accessible.
    */
    'disk' => 'local',

    /*
    |--------------------------------------------------------------------------
    | Max Upload Size (KB)
    |--------------------------------------------------------------------------
    | Default 2 048 KB = 2 MB. Override via EMPLOYEE_DOC_MAX_KB in .env.
    */
    'max_size_kb' => (int) env('EMPLOYEE_DOC_MAX_KB', 2048),

    /*
    |--------------------------------------------------------------------------
    | Allowed MIME Extensions
    |--------------------------------------------------------------------------
    */
    'allowed_types' => ['pdf', 'jpg', 'jpeg', 'png'],

    /*
    |--------------------------------------------------------------------------
    | Archive on Replace
    |--------------------------------------------------------------------------
    | When true, the old file is moved to an archive folder instead of deleted.
    */
    'archive_on_replace' => (bool) env('EMPLOYEE_DOC_ARCHIVE', true),

    /*
    |--------------------------------------------------------------------------
    | Document Types
    |--------------------------------------------------------------------------
    | 'mandatory' => true  — must be uploaded before the profile is considered
    |                        complete. Shown with a required indicator in UI.
    */
    'types' => [
        '10th_certificate'   => ['label' => '10th Certificate',   'mandatory' => true],
        '12th_certificate'   => ['label' => '12th Certificate',   'mandatory' => true],
        'degree_certificate' => ['label' => 'Degree Certificate',  'mandatory' => true],
        'experience_letter'  => ['label' => 'Experience Letter',   'mandatory' => false],
        'aadhaar_card'       => ['label' => 'Aadhaar Card',        'mandatory' => true],
        'pan_card'           => ['label' => 'PAN Card',            'mandatory' => true],
        'offer_letter'       => ['label' => 'Offer Letter',        'mandatory' => false],
    ],

];
