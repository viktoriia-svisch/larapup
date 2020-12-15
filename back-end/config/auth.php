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
        'api.coordinatorMaster' => [
            'driver' => 'jwt',
            'provider' => 'CoordinatorMaster',
        ],
    ],
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\User::class,
        ],
        'Student' => [
            'driver' => 'eloquent',
            'model' => \App\Models\Student::class,
        ],
        'Coordinator' => [
            'driver' => 'eloquent',
            'model' => \App\Models\Coordinator::class,
        ],
        'CoordinatorMaster' => [
            'driver' => 'eloquent',
            'model' => \App\Models\CoordinatorMaster::class,
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
