<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

final class KomoditasPotensiWisataEnum extends Enum
{
    public const AGROWISATA = 74;

    public const AIR_TERJUN = 80;

    public const ARUNG_JERAM = 78;

    public const CAGAR_BUDAYA = 77;

    public const DANAU = 72;

    public const GOA = 76;

    public const GUNUNG = 73;

    public const HUTAN_KHUSUS = 75;

    public const LAUT = 71;

    public const PADANG_SAVANA = 81;

    public const SITUS_SEJARAH = 79;

    public static function getDescription($value): string
    {
        return match ($value) {
            self::AGROWISATA => 'Agrowisata',
            self::AIR_TERJUN => 'Air Terjun',
            self::ARUNG_JERAM => 'Arung Jerum',
            self::CAGAR_BUDAYA => 'Cagar Budaya',
            self::DANAU => 'Danau (Wisata Air, Hutan Wisata, Situs Purbakala, dll)',
            self::GOA => 'Goa',
            self::GUNUNG => 'Gunung (Wisata Hutan, Taman Nasional, Bumi Perkemahan, dll)',
            self::HUTAN_KHUSUS => 'Hutan Khusus',
            self::LAUT => 'Laut (Wisata, Pulau, Taman Laut, Situs Sejarah Bahari, dll)',
            self::PADANG_SAVANA => 'Padang Savana(wisata Padang Savana)',
            self::SITUS_SEJARAH => 'Situs Sejarah, dan Museum',
        };
    }
}
