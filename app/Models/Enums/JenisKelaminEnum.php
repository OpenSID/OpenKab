<?php

namespace App\Models\Enums;

use Illuminate\Validation\Rules\Enum;

final class JenisKelaminEnum extends Enum
{
    public const laki_laki = 1;

    public const perempuan = 2;

    /**
     * Override method all().
     */
    public static function all(): array
    {
        return [
            self::laki_laki => 'Laki-laki',
            self::perempuan => 'Perempuan',
        ];
    }

    /**
     * Get label by value.
     */
    public static function getLabel(int $value): ?string
    {
        return self::all()[$value] ?? null;
    }
}
