<?php

namespace App\Models\Enums;

class SakitMenahunEnum extends BaseEnum
{
    public const JANTUNG = 1;

    public const LEVER = 2;

    public const PARU_PARU = 3;

    public const KANKER = 4;

    public const STROKE = 5;

    public const DIABETES_MELITUS = 6;

    public const GINJAL = 7;

    public const MALARIA = 8;

    public const LEPRA_KUSTA = 9;

    public const HIV_AIDS = 10;

    public const GILA_STRESS = 11;

    public const TBC = 12;

    public const ASTHMA = 13;

    public const ZOONOTIK = 15;

    public const TIDAK_ADA_TIDAK_SAKIT = 14;

    /**
     * Override method all().
     */
    public static function all(): array
    {
        return [
            self::JANTUNG => 'JANTUNG',
            self::LEVER => 'LEVER',
            self::PARU_PARU => 'PARU-PARU',
            self::KANKER => 'KANKER',
            self::STROKE => 'STROKE',
            self::DIABETES_MELITUS => 'DIABETES MELITUS',
            self::GINJAL => 'GINJAL',
            self::MALARIA => 'MALARIA',
            self::LEPRA_KUSTA => 'LEPRA/KUSTA',
            self::HIV_AIDS => 'HIV/AIDS',
            self::GILA_STRESS => 'GILA/STRESS',
            self::TBC => 'TBC',
            self::ASTHMA => 'ASTHMA',
            self::ZOONOTIK => 'ZOONOTIK',
            self::TIDAK_ADA_TIDAK_SAKIT => 'TIDAK ADA/TIDAK SAKIT',
        ];
    }

    /**
     * Dapatkan data dengan format id dan nama.
     */
    public static function getData(): array
    {
        $collect = collect(self::all());

        return $collect->map(static function ($value, $key) {
            return [
                'id' => $key,
                'nama' => $value,
            ];
        })->values()->toArray();
    }
}
