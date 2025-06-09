<?php

namespace App\Models\Enums;

class PendidikanKKEnum extends BaseEnum
{
    public const BELUM_SEKOLAH = 1;

    public const BELUM_TAMAT_SD = 2;

    public const TAMAT_SD = 3;

    public const SLTP = 4;

    public const SLTA = 5;

    public const DIPLOMA = 6;

    public const AKADEMI = 7;

    public const STRATA_I = 8;

    public const STRATA_II = 9;

    public const STRATA_III = 10;

    /**
     * Override method all().
     */
    public static function all(): array
    {
        return [
            self::BELUM_SEKOLAH => 'TIDAK/BELUM SEKOLAH',
            self::BELUM_TAMAT_SD => 'BELUM TAMAT SD/SEDERAJAT',
            self::TAMAT_SD => 'TAMAT SD/SEDERAJAT',
            self::SLTP => 'SLTP/SEDERAJAT',
            self::SLTA => 'SLTA/SEDERAJAT',
            self::DIPLOMA => 'DIPLOMA I/II',
            self::AKADEMI => 'AKADEMI/DIPLOMA III/S. MUDA',
            self::STRATA_I => 'DIPLOMA IV/STRATA I',
            self::STRATA_II => 'STRATA II',
            self::STRATA_III => 'STRATA III',
        ];
    }
}
