<?php

namespace App\Models\Enums;

use BenSampo\Enum\Enum;

final class StatusDasarEnum extends Enum
{
    const HIDUP = 1;

    const MATI = 2;

    const PINDAH = 3;

    const HILANG = 4;

    const PERGI = 6;

    const TIDAK_VALID = 9;

    /**
     * Override method all().
     */
    public static function all(): array
    {
        return [
            self::HIDUP => 'Hidup',
            self::MATI => 'Mati',
            self::PINDAH => 'Pindah',
            self::HILANG => 'Hilang',
            self::PERGI => 'Pergi',
            self::TIDAK_VALID => 'Tidak Valid',
        ];
    }
}
