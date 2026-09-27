<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    /*
    |--------------------------------------------------------------------------
    | SECURITY FIX: Restrict allowed origins to trusted domains only
    |--------------------------------------------------------------------------
    | Using wildcard (*) with supports_credentials=true is a security risk.
    | This allows only trusted origins to access the API with credentials.
    | Add production domains and localhost for development.
    |
    | Environment variable: CORS_ALLOWED_ORIGINS (comma-separated list)
    | Default includes production domain and local development URLs.
    */
    'allowed_origins' => array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', 'https://devopenkab.opendesa.id,http://localhost:3000,http://127.0.0.1:3000,http://localhost:5173,http://127.0.0.1:5173'))),

    'allowed_origins_patterns' => [],

    /*
    |--------------------------------------------------------------------------
    | Allowed Headers - Limit to necessary headers only
    |--------------------------------------------------------------------------
    | Avoid wildcard (*) in production. Only allow headers that are needed.
    */
    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'X-XSRF-TOKEN'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
