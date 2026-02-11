<?php

return [
    'hosts' => [
        [
            'host' => env('ELASTICSEARCH_HOST', 'localhost'),
            'port' => env('ELASTICSEARCH_PORT', '9200'),
            'scheme' => env('ELASTICSEARCH_SCHEME', 'http'),
            'user' => env('ELASTICSEARCH_USER', null),
            'pass' => env('ELASTICSEARCH_PASS', null),
        ],
    ],

    'indices' => [
        'students' => [
            'name' => env('ELASTICSEARCH_INDEX_STUDENTS', 'ums_students'),
            'settings' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
            ],
            'mappings' => [
                'properties' => [
                    'student_id' => ['type' => 'integer'],
                    'student_number' => ['type' => 'keyword'],
                    'first_name' => ['type' => 'text', 'analyzer' => 'standard'],
                    'last_name' => ['type' => 'text', 'analyzer' => 'standard'],
                    'full_name' => ['type' => 'text', 'analyzer' => 'standard'],
                    'email' => ['type' => 'keyword'],
                    'status' => ['type' => 'keyword'],
                    'major_name' => ['type' => 'text'],
                    'department_name' => ['type' => 'text'],
                    'college_name' => ['type' => 'text'],
                    'enrollment_date' => ['type' => 'date'],
                    'created_at' => ['type' => 'date'],
                ],
            ],
        ],
        'courses' => [
            'name' => env('ELASTICSEARCH_INDEX_COURSES', 'ums_courses'),
            'settings' => [
                'number_of_shards' => 1,
                'number_of_replicas' => 0,
            ],
            'mappings' => [
                'properties' => [
                    'course_id' => ['type' => 'integer'],
                    'course_code' => ['type' => 'keyword'],
                    'name' => ['type' => 'text', 'analyzer' => 'standard'],
                    'description' => ['type' => 'text', 'analyzer' => 'standard'],
                    'credit_hours' => ['type' => 'integer'],
                    'is_active' => ['type' => 'boolean'],
                    'created_at' => ['type' => 'date'],
                ],
            ],
        ],
    ],
];
