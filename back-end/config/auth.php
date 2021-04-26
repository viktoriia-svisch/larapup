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
            'driver' => 'session',
            'provider' => 'Student',
        ],
        'api.coordinator' => [
            'driver' => 'session',
            'provider' => 'Coordinator',
        ],
        'api.guest' => [
            'driver' => 'session',
            'provider' => 'Guest',
        ],
        'api.admin' => [
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
