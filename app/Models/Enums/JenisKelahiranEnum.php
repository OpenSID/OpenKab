<?php

namespace App\Models\Enums;

enum JenisKelahiranEnum: int {
    case tunggal = 1;
    case kembar_2 = 2;
    case kembar_3 = 3;
    case kembar_4 = 4;

    public function label(): string|null
    {
        return match ($this) {
            static::tunggal => 'Tunggal',
            static::kembar_2 => 'Kembar 2',
            static::kembar_3 => 'Kembar 3',
            static::kembar_4 => 'Kembar 4',
            default => null,
        };
    }
}