<?php

return [
    'endpoints' => [
        'desa_aktif' => 'desa-aktif',
        // 'kecamatan' => 'statistik-web/get-list-kecamatan',
        // 'desa' => 'statistik-web/get-list-desa',
        // 'data_presisi' => 'data-presisi/laporan',
        // 'artikel' => 'artikel/list',
        // 'artikel_terbit' => 'artikel-public/list',
        // 'coordinate' => 'statistik-web/get-list-coordinate',
        // 'penduduk' => 'statistik-web/penduduk',
        // 'keluarga' => 'statistik-web/keluarga',
        // 'rtm' => 'statistik-web/rtm',
        // 'bantuan' => 'statistik-web/bantuan',
        // 'data_summary' => 'data-summary',
        // 'data_website' => 'data-website',
    ],

    'default_timeout' => 30,

    'cache_ttl' => 3600,

    'enabled' => env('API_PROXY_ENABLED', true),
];
