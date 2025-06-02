<?php

namespace App\Enums;

class SasaranStatistikKeluargaEnum
{
    // public const PENDUDUK = 1;
    // public const KELUARGA = 2;
    // public const RUMAH_TANGGA = 3;
    // public const KELOMPOK = 4;

    // public static function getLabel(int $id): string
    // {
    //     return match ($id) {
    //         self::PENDUDUK => 'Penduduk',
    //         self::KELUARGA => 'Keluarga',
    //         self::RUMAH_TANGGA => 'Rumah Tangga',
    //         self::KELOMPOK => 'Kelompok',
    //         default => 'Tidak diketahui',
    //     };
    // }

    public const SASARAN_PENDUDUK = 1;

    public const SASARAN_KELUARGA = 2;

    public const SASARAN_RUMAH_TANGGA = 3;

    public const SASARAN_KELOMPOK = 4;

    public const KATEGORI_STATISTIK = [
        'kelas-sosial' => 'Kelas Sosial',
    ];
}
