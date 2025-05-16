<?php
namespace App\Models\Enums;


class CacatEnum extends BaseEnum
{
    public const CACAT_FISIK            = 1;
    public const CACAT_NETRA_BUTA       = 2;
    public const CACAT_RUNGU_WICARA     = 3;
    public const CACAT_MENTAL_JIWA      = 4;
    public const CACAT_FISIK_DAN_MENTAL = 5;
    public const CACAT_LAINNYA          = 6;
    public const TIDAK_CACAT            = 7;

    /**
     * Override method all()
     */
    public static function all(): array
    {
        return [
            self::CACAT_FISIK            => 'CACAT FISIK',
            self::CACAT_NETRA_BUTA       => 'CACAT NETRA/BUTA',
            self::CACAT_RUNGU_WICARA     => 'CACAT RUNGU/WICARA',
            self::CACAT_MENTAL_JIWA      => 'CACAT MENTAL/JIWA',
            self::CACAT_FISIK_DAN_MENTAL => 'CACAT FISIK DAN MENTAL',
            self::CACAT_LAINNYA          => 'CACAT LAINNYA',
            self::TIDAK_CACAT            => 'TIDAK CACAT',
        ];
    }
}
