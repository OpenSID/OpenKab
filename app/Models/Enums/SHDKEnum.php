<?php

namespace App\Models\Enums;

use BenSampo\Enum\Enum;

class SHDKEnum extends Enum
{
    public const KEPALA_KELUARGA = 1;

    public const SUAMI = 2;

    public const ISTRI = 3;

    public const ANAK = 4;

    public const MENANTU = 5;

    public const CUCU = 6;

    public const ORANGTUA = 7;

    public const MERTUA = 8;

    public const FAMILI_LAIN = 9;

    public const PEMBANTU = 10;

    public const LAINNYA = 11;

    /**
     * Override method all().
     */
    public static function all(): array
    {
        return [
            self::KEPALA_KELUARGA => 'KEPALA KELUARGA',
            self::SUAMI => 'SUAMI',
            self::ISTRI => 'ISTRI',
            self::ANAK => 'ANAK',
            self::MENANTU => 'MENANTU',
            self::CUCU => 'CUCU',
            self::ORANGTUA => 'ORANGTUA',
            self::MERTUA => 'MERTUA',
            self::FAMILI_LAIN => 'FAMILI LAIN',
            self::PEMBANTU => 'PEMBANTU',
            self::LAINNYA => 'LAINNYA',
        ];
    }
}
