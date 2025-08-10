<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting Configuration
    |--------------------------------------------------------------------------
    */

    'default' => [
        'max_attempts' => 60,
        'window_minutes' => 1,
    ],

    'api' => [
        'max_attempts' => 100,
        'window_minutes' => 1,
    ],

    'login' => [
        'max_attempts' => 5,
        'window_minutes' => 15,
    ],

    'register' => [
        'max_attempts' => 3,
        'window_minutes' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limit Storage
    |--------------------------------------------------------------------------
    | Options: session, redis, memcached, database
    */

    'storage' => 'session',

    /*
    |--------------------------------------------------------------------------
    | Rate Limit Response
    |--------------------------------------------------------------------------
    */

    'response' => [
        'status_code' => 429,
        'message' => 'Too Many Requests',
    ],
];
