<?php

namespace App\Models\Enums;

enum TempatDilahirkanEnum: int {
    case rs_rb = 1;
    case puskesmas = 2;
    case polindes = 3;
    case rumah = 4;
    case lainnya = 5;

    public function label(): string|null
    {
        return match($this) {
            static::rs_rb => 'RS/RB',
            static::puskesmas => 'Puskesmas',
            static::polindes => 'Polindes',
            static::rumah => 'Rumah',
            static::lainnya => 'Lainnya',
            default => null
        };
    }
}