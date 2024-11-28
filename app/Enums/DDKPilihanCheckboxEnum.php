<?php

namespace App\Enums;

class DDKPilihanCheckboxEnum
{
    public const SUMBER_AIR_MINUM = [
        1 => 'Mata air',
        2 => 'Sumur gali',
        3 => 'Sumur pompa',
        4 => 'Hidran umum',
        5 => 'PAM',
        6 => 'Pipa',
        7 => 'Sungai',
        8 => 'Embung',
        9 => 'Bak penampung air hujan',
        10 => 'Beli dari tangki swasta',
        11 => 'Depot isi ulang',
    ];

    public const SUMBER_AIR_MINUM_CHECKBOX = [
        1 => 'Baik',
        2 => 'Berasa',
        3 => 'Berwarna',
        4 => 'Berbau',
        5 => 'Berasa dan Berwarna', // Berasa + Berwarna = 2 + 3
        6 => 'Berasa dan Berbau', // 2 + 4
        7 => 'Berwarna dan Berbau', // 3 + 4
        9 => 'Berasa, Berwarna dan Berbau', // 2 + 3 + 4
    ];

    public const KEPEMILIKAN_LAHAN = [
        1 => 'Lahan Tanaman Pangan',
        2 => 'Lahan Tanaman Perkebunan',
        3 => 'Lahan Hutan',
    ];

    public const KEPEMILIKAN_LAHAN_CHECKBOX = [
        1 => 'Memiliki kurang 0,5 ha',
        2 => 'Memiliki 0,5 - 1,0 ha',
        3 => 'Memiliki lebih dari 1,0 ha',
        4 => 'Tidak memiliki',
    ];

    /**
     * prodeskel_ddk_detail ~ checkbox.
     */
    final public static function semuaCheckbox(): array
    {
        return [
            DDKEnum::KODE_SUMBER_AIR_MINUM => self::SUMBER_AIR_MINUM,
            DDKEnum::KODE_SUMBER_AIR_MINUM_CHECKBOX => self::SUMBER_AIR_MINUM_CHECKBOX,
            DDKEnum::KODE_KEPEMILIKAN_LAHAN => self::KEPEMILIKAN_LAHAN,
            DDKEnum::KODE_KEPEMILIKAN_LAHAN_CHECKBOX => self::KEPEMILIKAN_LAHAN_CHECKBOX,
        ];
    }

    public static function semuaKode(): array
    {
        return [
            DDKEnum::KODE_SUMBER_AIR_MINUM,
            DDKEnum::KODE_SUMBER_AIR_MINUM_CHECKBOX,
            DDKEnum::KODE_KEPEMILIKAN_LAHAN,
            DDKEnum::KODE_KEPEMILIKAN_LAHAN_CHECKBOX,
        ];
    }

    public static function getKodeSumberAirMinum($nilai): array
    {
        if ($nilai <= 4) {
            return [$nilai];
        }
        if ($nilai == 5) {
            return [2, 3];
        }
        if ($nilai == 6) {
            return [2, 4];
        }
        if ($nilai == 7) {
            return [3, 4];
        }
        if ($nilai == 9) {
            return [2, 3, 4];
        }
    }
}
