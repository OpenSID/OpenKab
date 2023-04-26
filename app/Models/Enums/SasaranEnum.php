<?php

namespace App\Models\Enums;

use BenSampo\Enum\Enum;
use App\Models\Traits\EnumToArrayTrait;

final class SasaranEnum extends Enum
{
    use EnumToArrayTrait;

    const PENDUDUK = 1;
    const KELUARGA = 2;
    const RUMAH_TANGGA = 3;
    const KELOMPOK = 4;

    public function label(): string|null
    {
        return match ($this) {
            static::PENDUDUK => 'Penduduk',
            static::KELUARGA => 'Keluarga',
            static::RUMAH_TANGGA => 'Rumah Tangga',
            static::KELOMPOK => 'Kelompok/Organisasi Kemasyarakatan',
            default => null
        };
    }
}
