<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Registration Module Configuration
    |--------------------------------------------------------------------------
    */

    // Default pagination size for listing endpoints
    'per_page' => env('REGISTRATION_PER_PAGE', 15),

    // Maximum number of sections allowed per course per semester
    'max_sections_per_course' => env('REGISTRATION_MAX_SECTIONS', 10),

    // Allowed semester values
    'semesters' => [
        'Fall',
        'Spring',
        'Summer',
    ],

    // Days of the week available for scheduling
    'schedule_days' => [
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    ],

    // Minimum and maximum credit hours
    'credit_hours' => [
        'min' => 1,
        'max' => 6,
    ],
];
