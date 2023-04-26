<?php

namespace App\Models\Enums;

use App\Models\Traits\EnumToArrayTrait;
use BenSampo\Enum\Enum;

final class SasaranEnum extends Enum
{
    use EnumToArrayTrait;

    const PENDUDUK = 1;

    const KELUARGA = 2;

    const RUMAH_TANGGA = 3;

    const KELOMPOK = 4;

    public static function getAll(): array
    {
        return [
            static::PENDUDUK => 'Penduduk',
            static::KELUARGA => 'Keluarga',
            static::RUMAH_TANGGA => 'Rumah Tangga',
            static::KELOMPOK => 'Kelompok/Organisasi Kemasyarakatan',
        ];
    }

    public static function getDescription($value): string
    {
        return self::getAll()[$value];
    }
}
