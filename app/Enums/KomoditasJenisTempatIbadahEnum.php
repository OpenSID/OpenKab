<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class KomoditasJenisTempatIbadahEnum extends Enum
{
    public const JUMLAH_GEREJA_KATHOLIK = 555;

    public const JUMLAH_GEREJA_KRISTEN_PROTESTAN = 554;

    public const JUMLAH_KLENTENG = 558;

    public const JUMLAH_LANGGAR_SURAU_MUSHOLA = 553;

    public const JUMLAH_MASJID = 552;

    public const JUMLAH_PURA = 557;

    public const JUMLAH_WIHARA = 556;

    public static function getDescription($value): string
    {
        return match ($value) {
            self::JUMLAH_GEREJA_KATHOLIK => 'Jumlah Gereja Katholik',
            self::JUMLAH_GEREJA_KRISTEN_PROTESTAN => 'Jumlah Gereja Kristen Protestan',
            self::JUMLAH_KLENTENG => 'Jumlah Klenteng',
            self::JUMLAH_LANGGAR_SURAU_MUSHOLA => 'Jumlah Langgar/Surau/Mushola',
            self::JUMLAH_MASJID => 'Jumlah Masjid',
            self::JUMLAH_PURA => 'Jumlah Pura',
            self::JUMLAH_WIHARA => 'Jumlah Wihara',
            default => parent::getDescription($value),
        };
    }
}
