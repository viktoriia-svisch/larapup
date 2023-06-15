<?php
use App\Models\Admin;
use App\Models\Coordinator;
use App\Models\Guest;
use App\Models\Student;
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
            'model' => Guest::class,
        ],
        'student' => [
            'driver' => 'eloquent',
            'model' => Student::class,
        ],
        'coordinator' => [
            'driver' => 'eloquent',
            'model' => Coordinator::class,
        ],
        'admin' => [
            'driver' => 'eloquent',
            'model' => Admin::class,
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
