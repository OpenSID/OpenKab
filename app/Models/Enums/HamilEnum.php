<?php

namespace App\Models\Enums;

class HamilEnum extends BaseEnum
{
    public const HAMIL = 1;

    public const TIDAK_HAMIL = 2;

    /**
     * Override method all().
     */
    public static function all(): array
    {
        return [
            self::HAMIL => 'Hamil',
            self::TIDAK_HAMIL => 'Tidak Hamil',
        ];
    }
}
