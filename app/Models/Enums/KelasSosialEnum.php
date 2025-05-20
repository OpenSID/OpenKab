<?php

namespace App\Models\Enums;

final class KelasSosialEnum extends BaseEnum
{
    const PRA_SEJAHTERA = 1;
    const SEJAHTERA_I = 2;
    const SEJAHTERA_II = 3;
    const SEJAHTERA_III = 4;
    const SEJAHTERA_III_PLUS = 5;

    /**
     * Override method all().
     */
    public static function all(): array
    {
        return [
            self::PRA_SEJAHTERA => 'Keluarga Pra Sejahtera',
            self::SEJAHTERA_I => 'Keluarga Sejahtera I',
            self::SEJAHTERA_II => 'Keluarga Sejahtera II',
            self::SEJAHTERA_III => 'Keluarga Sejahtera III',
            self::SEJAHTERA_III_PLUS => 'Keluarga Sejahtera III Plus',
        ];
    }
}
