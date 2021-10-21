<?php
return [
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'Student',
        ],
        'api' => [
            'driver' => 'token',
            'provider' => 'users',
            'hash' => false,
        ],
        'student' => [
            'driver' => 'session',
            'provider' => 'Student',
        ],
        'coordinator' => [
            'driver' => 'session',
            'provider' => 'Coordinator',
        ],
        'guest' => [
            'driver' => 'session',
            'provider' => 'Guest',
        ],
        'admin' => [
            'driver' => 'session',
            'provider' => 'Admin',
        ],
    ],
    'providers' => [
        'Guest' => [
            'driver' => 'eloquent',
            'model' => \App\Models\Guest::class,
        ],
        'Student' => [
            'driver' => 'eloquent',
            'model' => \App\Models\Student::class,
        ],
        'Coordinator' => [
            'driver' => 'eloquent',
            'model' => \App\Models\Coordinator::class,
        ],
        'Admin' => [
            'driver' => 'eloquent',
            'model' => \App\Models\Admin::class,
        ]
    ],
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'password_resets',
            'expire' => 60,
        ],
    ],
];
