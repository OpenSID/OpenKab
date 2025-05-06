<?php

namespace App\Enums;

class LokasiStatusEnum
{
    public const LOCK = 1;

    public const UNLOCK = 2;

    public static function getLabel(int $value): string
    {
        return match ($value) {
            self::LOCK => 'Terkunci',
            self::UNLOCK => 'Terbuka',
            default => 'Tidak diketahui',
        };
    }
}
