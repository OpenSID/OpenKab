<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Service Klien API Satu Data (DataSpasial)
    |--------------------------------------------------------------------------
    |
    | OpenKab TIDAK terhubung ke database gabungan dan tidak memiliki tabel
    | DataSpasial. Seluruh baca dan tulis data `ds_*` melewati API Satu Data
    | melalui service `App\Services\DataSpasialApiService`.
    |
    | Base URL dan token TIDAK dikonfigurasi di sini: keduanya otomatis
    | mengikuti pengaturan database gabungan yang sudah ada —
    |   - URL  : config('app.databaseGabunganUrl') / env API_DATABASE_GABUNGAN_HOST
    |   - token: Setting `database_gabungan_api_key`
    | Berkas ini hanya menyimpan setelan operasional.
    |
    */

    /*
    | Batas waktu permintaan dalam detik.
    */
    'timeout' => (int) env('API_DATASPASIAL_TIMEOUT', 10),

    /*
    | Masa berlaku cache operasi baca dalam detik. 0 berarti cache nonaktif.
    */
    'cache_ttl' => (int) env('API_DATASPASIAL_CACHE_TTL', 300),

    /*
    | Store cache yang dipakai. null berarti memakai default aplikasi.
    */
    'cache_store' => env('API_DATASPASIAL_CACHE_STORE'),

    /*
    | Prefix kunci cache operasi baca. Memungkinkan invalidasi seluruh cache
    | baca tanpa menyentuh cache modul lain.
    */
    'cache_prefix' => 'dataspasial:get:',

    /*
    | Header identitas pemanggil yang dibaca API untuk audit (API-07).
    */
    'user_id_header' => 'X-OpenKab-User-Id',

    'user_name_header' => 'X-OpenKab-User-Name',

    /*
    | Prefix path endpoint DataSpasial di sisi API.
    */
    'path_prefix' => '/api/v1/dataspasial',

    /*
    | Pesan yang ditampilkan ke pengguna saat data tidak dapat diperoleh.
    | WAJIB generik — detail teknis hanya untuk log backend.
    */
    'unavailable_message' => 'Data sementara tidak tersedia. Silakan coba lagi nanti.',

];
