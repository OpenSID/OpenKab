<?php

namespace App\Enums;

class KategoriSasaranBantuanEnum
{
    public const SASARAN_PENDUDUK = 1;

    public const SASARAN_KELUARGA = 2;

    public const SASARAN_RUMAH_TANGGA = 3;

    public const SASARAN_KELOMPOK = 4;

    public const KATEGORI_STATISTIK = [
        'penduduk' => 'Penerima Bantuan Penduduk',
        'keluarga' => 'Penerima Bantuan Keluarga',
        // 'rtm' => 'Penerima Bantuan Rumah Tangga',
        // 'kelompok' => 'Penerima Bantuan Kelompok',
    ];
}
