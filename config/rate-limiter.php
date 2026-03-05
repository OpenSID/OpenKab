<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Global Rate Limiter Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration controls the global rate limiting for all requests
    | to your application. You may configure these values in your .env file.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Enable Global Rate Limiter
    |--------------------------------------------------------------------------
    |
    | This option controls whether the global rate limiter is enabled or not.
    | When set to false, the rate limiter will be bypassed for all requests.
    |
    */
    'enabled' => env('RATE_LIMITER_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Maximum Attempts
    |--------------------------------------------------------------------------
    |
    | This value controls the maximum number of requests that can be made
    | within the given decay period. Once this limit is reached, subsequent
    | requests will be blocked until the decay period has elapsed.
    |
    */
    'max_attempts' => env('RATE_LIMITER_MAX_ATTEMPTS', 60),

    /*
    |--------------------------------------------------------------------------
    | Decay Minutes
    |--------------------------------------------------------------------------
    |
    | This value controls the number of minutes to wait before the rate
    | limiter resets. After this period, the request count will be reset
    | to zero and new requests will be allowed.
    |
    */
    'decay_minutes' => env('RATE_LIMITER_DECAY_MINUTES', 1),

    /*
    |--------------------------------------------------------------------------
    | Exclude Paths
    |--------------------------------------------------------------------------
    |
    | This array contains the paths that should be excluded from the
    | global rate limiting. These paths will not be subject to the
    | rate limiting rules defined above.
    |
    */
    'exclude_paths' => [
        // 'api/health',
        // 'api/ping',
        // 'admin/*',
    ],

    /*
    |--------------------------------------------------------------------------
    | Exclude IP Addresses
    |--------------------------------------------------------------------------
    |
    | This array contains the IP addresses that should be excluded from
    | the global rate limiting. These IP addresses will not be subject
    | to the rate limiting rules defined above.
    |
    */
    'exclude_ips' => [
        // '127.0.0.1',
        // '192.168.1.1',
    ],
];