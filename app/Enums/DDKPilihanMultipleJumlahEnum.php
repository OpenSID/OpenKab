<?php

namespace App\Enums;

class DDKPilihanMultipleJumlahEnum
{
    public const KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI = [
        1 => 'Sapi',
        2 => 'Kerbau',
        3 => 'Babi',
        4 => 'Ayam kampung',
        5 => 'Ayam Broiler',
        6 => 'Bebek',
        7 => 'Kuda',
        8 => 'Kambing',
        9 => 'Domba',
        10 => 'Angsa',
        11 => 'Burung Puyuh',
        12 => 'Kelinci',
        13 => 'Burung walet',
        14 => 'Anjing',
        15 => 'Kucing',
        16 => 'Ular cobra',
        17 => 'Burung Onta',
        18 => 'Ular pithon',
        19 => 'Burung cendrawasih',
        20 => 'Burung kakatua',
        21 => 'Burung beo',
        22 => 'Burung merak',
        23 => 'Burung langka lainnya',
        24 => 'Buaya',
    ];

    public const ALAT_PRODUKSI_BUDIDAYA_IKAN = [
        1 => 'Karamba',
        2 => 'Tambak',
        3 => 'Jermal',
        4 => 'Pancing',
        5 => 'Pukat',
        6 => 'Jala',
    ];

    public const KUALITAS_PERTOLONGAN_PERSALINAN = [
        1 => 'Jumlah Persalinan ditolong Dokter',
        2 => 'Jumlah persalinan ditolong bidan',
        3 => 'Jumlah persalinan ditolong perawat',
        4 => 'Jumlah persalinan ditolong dukun bersalin',
        5 => 'Jumlah persalinan ditolong keluarga',
    ];

    /**
     * prodeskel_ddk_detail ~ jumlah.
     */
    final public static function semuaJumlah(): array
    {
        return [
            DDKEnum::KODE_KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI => self::KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI,
            DDKEnum::KODE_ALAT_PRODUKSI_BUDIDAYA_IKAN => self::ALAT_PRODUKSI_BUDIDAYA_IKAN,
            DDKEnum::KODE_KUALITAS_PERTOLONGAN_PERSALINAN => self::KUALITAS_PERTOLONGAN_PERSALINAN,
        ];
    }

    public static function semuaKode(): array
    {
        return [
            DDKEnum::KODE_KEPEMILIKAN_JENIS_TERNAK_KELUARGA_TAHUN_INI,
            DDKEnum::KODE_ALAT_PRODUKSI_BUDIDAYA_IKAN,
            DDKEnum::KODE_KUALITAS_PERTOLONGAN_PERSALINAN,
        ];
    }
}
