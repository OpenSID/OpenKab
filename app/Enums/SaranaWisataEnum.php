<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class SaranaWisataEnum extends Enum
{
    public const BILYAR = 612;

    public const BIOSKOP = 616;

    public const DISKOTIK = 611;

    public const HOTEL_BINTANG_1 = 609;

    public const HOTEL_BINTANG_2 = 608;

    public const HOTEL_BINTANG_3 = 607;

    public const HOTEL_BINTANG_4 = 606;

    public const HOTEL_BINTANG_5 = 605;

    public const HOTEL_MELATI = 610;

    public const JUMLAH_TEMPAT_WISATA = 604;

    public const KARAOKE = 613;

    public const MUSEUM = 614;

    public const PRASARANA_HIBURAN_DAN_WISATA_LAINNYA = 2203;

    public const RESTORAN = 615;

    public static function getDescription($value): string
    {
        return match ($value) {
            self::BILYAR => 'Bilyar',
            self::BIOSKOP => 'Bioskop',
            self::DISKOTIK => 'Diskotik',
            self::HOTEL_BINTANG_1 => 'Hotel bintang 1',
            self::HOTEL_BINTANG_2 => 'Hotel bintang 2',
            self::HOTEL_BINTANG_3 => 'Hotel bintang 3',
            self::HOTEL_BINTANG_4 => 'Hotel bintang 4',
            self::HOTEL_BINTANG_5 => 'Hotel bintang 5',
            self::HOTEL_MELATI => 'Hotel melati',
            self::JUMLAH_TEMPAT_WISATA => 'Jumlah Tempat Wisata',
            self::KARAOKE => 'Karaoke',
            self::MUSEUM => 'Museum',
            self::PRASARANA_HIBURAN_DAN_WISATA_LAINNYA => 'Prasarana Hiburan dan Wisata Lainnya',
            self::RESTORAN => 'Restoran',
            default => parent::getDescription($value),
        };
    }
}
