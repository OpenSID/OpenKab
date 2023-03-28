<?php

namespace App\Models\Enums;

enum PenolongKelahiranEnum: int {
    case dokter = 1;
    case bidan_perawat = 2;
    case dukun = 3;
    case lainnya = 4;

    public function label(): string|null
    {
        return match ($this) {
            static::dokter => 'Dokter',
            static::bidan_perawat => 'Bidan Perawat',
            static::dukun => 'Dukun',
            static::lainnya => 'Lainnya',
            default => null,
        };
    }
}