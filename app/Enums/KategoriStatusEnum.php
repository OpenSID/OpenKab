<?php

namespace App\Enums;

class KategoriStatusEnum
{
    public const ENABLE = 1;

    public const DISABLE = 0;

    public static function getLabel(int $value): string
    {
        return match ($value) {
            self::ENABLE => 'Aktif',
            self::DISABLE => 'Tidak Aktif',
            default => 'Tidak Dikenal',
        };
    }
}
