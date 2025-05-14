<?php

namespace App\Models\Enums;

use BenSampo\Enum\Enum;

final class StatusDasarKKEnum extends Enum
{
    public const AKTIF = 1;

    public const HILANG_MATI_PINDAH = 2;

    public const KOSONG = 3;

    /**
     * Override method all().
     */
    public static function all(): array
    {
        return [
            self::AKTIF => 'KK Aktif',
            self::HILANG_MATI_PINDAH => 'KK Hilang/Pindah/Mati',
            self::KOSONG => 'KK Kosong',
        ];
    }
}
