<?php

namespace App\Models\Enums;

use Illuminate\Validation\Rules\Enum;

final class StatusEnum extends Enum
{
    const tidakAktif = 0;

    const aktif = 1;

    public const YA = 1;

    public const TIDAK = 0;

    /**
     * Override method all().
     */
    public static function all(): array
    {
        return [
            self::YA => 'Ya',
            self::TIDAK => 'Tidak',
        ];
    }
}
