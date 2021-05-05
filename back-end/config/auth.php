<?php
return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],
    'guards' => [
        'web' => [
            'driver' => 'jwt',
            'provider' => 'Student',
        ],
        'api' => [
            'driver' => 'jwt',
            'provider' => 'Student',
        ],
        'api.coordinator' => [
            'driver' => 'jwt',
            'provider' => 'Coordinator',
        ],
        'api.guest' => [
            'driver' => 'jwt',
            'provider' => 'Guest',
        ],
        'api.admin' => [
            'driver' => 'jwt',
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
