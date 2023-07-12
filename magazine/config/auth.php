<?php
return [
    'defaults' => [
        'guard' => 'api',
        'passwords' => 'users',
    ],
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'student',
        ],
        'api' => [
            'driver' => 'token',
            'provider' => 'guest',
            'hash' => false,
        ],
        'student' => [
            'driver' => 'session',
            'provider' => 'student',
        ],
        'coordinator' => [
            'driver' => 'session',
            'provider' => 'coordinator',
        ],
        'guest' => [
            'driver' => 'session',
            'provider' => 'guest',
        ],
        'admin' => [
            'driver' => 'session',
            'provider' => 'admin',
        ],
    ],
    'providers' => [
        'guest' => [
            'driver' => 'eloquent',
            'model' => \App\Models\Guest::class,
        ],
        'student' => [
            'driver' => 'eloquent',
            'model' => \App\Models\Student::class,
        ],
        'coordinator' => [
            'driver' => 'eloquent',
            'model' => \App\Models\Coordinator::class,
        ],
        'admin' => [
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
