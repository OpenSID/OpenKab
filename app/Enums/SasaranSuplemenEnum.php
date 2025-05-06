<?php

namespace App\Enums;

class SasaranSuplemenEnum
{
    public const PENDUDUK = 1;
    public const KELUARGA = 2;

    public static function getLabel(int $value): string
    {
        return match ($value) {
            self::PENDUDUK => 'Penduduk',
            self::KELUARGA => 'Keluarga',
            default => 'Tidak diketahui',
        };
    }
}
