<?php

namespace App\Models\Enums;

use App\Models\Traits\EnumToArrayTrait;

enum SasaranEnum: int {
    use EnumToArrayTrait;

    case PENDUDUK = 1;
    case KELUARGA = 2;
    case RUMAH_TANGGA = 3;
    case KELOMPOK = 4;

    public function label(): string|null
    {
        return match($this) {
            static::PENDUDUK => 'Penduduk',
            static::KELUARGA => 'Keluarga',
            static::RUMAH_TANGGA => 'Rumah Tangga',
            static::KELOMPOK => 'Kelompok/Organisasi Kemasyarakatan',
            default => null
        };
    }
}
