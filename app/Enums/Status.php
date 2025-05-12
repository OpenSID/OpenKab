<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Status untuk melihat aktif dan tidak aktif
 */
final class Status extends Enum
{
    public const TidakAktif = 0;

    public const Aktif = 1;

    public static function getDescription($value): string
    {
        return match ($value) {
            self::TidakAktif => 'Tidak Aktif',
            self::Aktif => 'Aktif',
        };
    }
}
