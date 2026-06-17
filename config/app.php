<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'OpenKab'),

    // TODO:: hapus ini jika sudah ada pengaturan tersendiri
    'namaKab' => env('APP_NAMA_KAB', 'Nama Kabupaten'),
    'namaProv' => env('APP_NAMA_PROV', 'Nama Provinsi'),
    'sebutanKab' => env('APP_SEBUTAN_KAB', 'Kota'),
    'sebutanDesa' => env('APP_SEBUTAN_DESA', 'Desa'),
    'serverPantau' => env('APP_PANTAU', 'https://pantau.opensid.my.id/'),
    'tokenPantau' => env('TOKEN_PANTAU', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6bnVsbCwidGltZXN0YW1wIjoxNjAzNDY2MjM5fQ.HVCNnMLokF2tgHwjQhSIYo6-2GNXB4-Kf28FSIeXnZw'),

    'namaAplikasi' => env('APP_NAMA_APLIKASI', 'Simatik'),
    'demo' => env('APP_DEMO', false),
    'databaseGabunganUrl' => env('API_DATABASE_GABUNGAN_HOST', 'https://api-database-gabungan.id/'),

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),

    'asset_url' => env('ASSET_URL'),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'id',

    'fallback_locale' => 'id',

    'faker_locale' => 'id_ID',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key' => env('APP_KEY'),

    'cipher' => 'AES-256-CBC',

    'previous_keys' => [
        ...array_filter(
            explode(',', (string) env('APP_PREVIOUS_KEYS', ''))
        ),
    ],

    /*
    |--------------------------------------------------------------------------
    | Maintenance Mode Driver
    |--------------------------------------------------------------------------
    |
    | These configuration options determine the driver used to determine and
    | manage Laravel's "maintenance mode" status. The "cache" driver will
    | allow maintenance mode to be controlled across multiple machines.
    |
    | Supported drivers: "file", "cache"
    |
    */

    'maintenance' => [
        'driver' => 'file',
        // 'store'  => 'redis',
    ],

    'format' => [
        'date' => env('FORMAT_DATE', 'd/m/Y'),
        'date_js' => env('FORMAT_DATE_JS', 'DD/MM/YYYY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | OTP Configuration
    |--------------------------------------------------------------------------
    |
    | These configuration values control the OTP token generation and validation.
    | You may configure these values in your .env file.
    |
    */

    'otp_token_expires_minutes' => (int) env('OTP_TOKEN_EXPIRES_MINUTES', 5),
    'otp_max_verification_attempts' => (int) env('OTP_MAX_VERIFICATION_ATTEMPTS', 3),
    'otp_length' => (int) env('OTP_LENGTH', 6),

    /*
    |--------------------------------------------------------------------------
    | OTP Rate Limiter Configuration
    |--------------------------------------------------------------------------
    |
    | These configuration values control the rate limiting for OTP operations.
    | You may configure these values in your .env file.
    |
    */

    'otp_setup_max_attempts' => (int) env('OTP_SETUP_MAX_ATTEMPTS', 3),
    'otp_setup_decay_seconds' => (int) env('OTP_SETUP_DECAY_SECONDS', 300),
    'otp_verify_max_attempts' => (int) env('OTP_VERIFY_MAX_ATTEMPTS', 5),
    'otp_verify_decay_seconds' => (int) env('OTP_VERIFY_DECAY_SECONDS', 300),
    'otp_resend_max_attempts' => (int) env('OTP_RESEND_MAX_ATTEMPTS', 2),
    'otp_resend_decay_seconds' => (int) env('OTP_RESEND_DECAY_SECONDS', 30),

    /*
    |--------------------------------------------------------------------------
    | Account Lockout & Progressive Delay Configuration
    |--------------------------------------------------------------------------
    |
    | These configuration values control the account lockout mechanism and
    | progressive delay for failed authentication attempts.
    | You may configure these values in your .env file.
    |
    */

    'account_lockout_max_attempts' => (int) env('ACCOUNT_LOCKOUT_MAX_ATTEMPTS', 5),
    'account_lockout_decay_minutes' => (int) env('ACCOUNT_LOCKOUT_DECAY_MINUTES', 15),
    'progressive_delay_base_seconds' => (int) env('PROGRESSIVE_DELAY_BASE_SECONDS', 2),
    'progressive_delay_multiplier' => (int) env('PROGRESSIVE_DELAY_MULTIPLIER', 2),
];
