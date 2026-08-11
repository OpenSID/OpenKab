<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Konfigurasi Single Sign-On OpenSID
    |--------------------------------------------------------------------------
    | Token ditandatangani dengan RS256 (RSA-2048). Private key HANYA di
    | OpenKab (env/file); public key didistribusikan ke OpenSID. Secret callback
    | (verify-token) minimal 32 byte. Validasi dipaksa saat digunakan
    | (SsoKeyManager & SsoCallbackAuth).
    */

    // Masa berlaku token SSO (detik). Maksimum 600 detik.
    'token_ttl' => (int) env('SSO_TOKEN_TTL', 300),

    // Batas atas masa berlaku token (detik) — tidak dapat dilewati lewat env.
    'token_ttl_max' => 600,

    // Toleransi selisih jam antar sistem (detik).
    'clock_skew_tolerance' => (int) env('SSO_CLOCK_SKEW_TOLERANCE', 30),

    // Private key RS256 (nilai PEM langsung) — HANYA di OpenKab.
    'signing_private_key' => (string) env('SSO_SIGNING_PRIVATE_KEY', ''),

    // Private key RS256 dari file PEM (relatif terhadap base_path bila bukan absolut).
    'signing_private_key_file' => (string) env('SSO_SIGNING_PRIVATE_KEY_FILE', ''),

    // Public key RS256 aktif (nilai PEM langsung); versi yang sama diberikan ke OpenSID.
    'signing_public_key' => (string) env('SSO_SIGNING_PUBLIC_KEY', ''),

    // Public key RS256 aktif dari file PEM.
    'signing_public_key_file' => (string) env('SSO_SIGNING_PUBLIC_KEY_FILE', ''),

    // Public key tambahan (file PEM, koma) untuk rotasi transisi.
    'signing_public_keys_file' => array_values(array_filter(array_map('trim', explode(',', (string) env('SSO_SIGNING_PUBLIC_KEYS_FILE', ''))))),

    // Sekret callback verify-token (dibagikan ke OpenSID).
    'callback_secret' => (string) env('SSO_CALLBACK_SECRET', ''),

    // Daftar IP yang diizinkan memanggil endpoint callback (kosong = semua).
    'ip_whitelist' => array_values(array_filter(array_map('trim', explode(',', (string) env('SSO_IP_WHITELIST', ''))))),

    // Maksimum permintaan generate-session per menit per user+IP.
    'rate_limit_max' => (int) env('SSO_RATE_LIMIT_MAX', 5),

    // Environment yang melewati validasi asal (Origin/Referer) di generate-session
    // agar alur development/testing tidak perlu mengirim header Origin. Daftar ini
    // dapat dikosongkan lewat config untuk memaksa validasi (dipakai di pengujian).
    'origin_check_skip_envs' => ['local', 'testing'],

    // Klaim iss / aud pada token.
    'issuer' => env('APP_URL', 'openkab'),

    'audience' => env('SSO_AUDIENCE', 'opensid'),

    'endpoint' => [
        // Path login SSO di sisi OpenSID (relatif terhadap base URL OpenSID).
        'sso_login' => 'admin/sso-login',
        // Path verifikasi di sisi OpenKab.
        'verify' => '/api/v1/sso/verify-token',
    ],
];
