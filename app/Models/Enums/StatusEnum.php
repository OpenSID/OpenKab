<?php

namespace App\Models\Enums;

enum StatusEnum: int {
    case tidakAktif = 0;
    case aktif      = 1;

    public function label(): string|null
    {
        return match ($this) {
            static::tidakAktif => 'Tidak Aktif',
            static::aktif      => 'Aktif',
            default => null,
        };
    }
}