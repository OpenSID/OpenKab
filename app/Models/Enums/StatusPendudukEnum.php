<?php

namespace App\Models\Enums;

class StatusPendudukEnum extends BaseEnum
{
    public const TETAP = 1;

    public const TIDAK_TETAP = 2;

    /**
     * Override method all().
     */
    public static function all(): array
    {
        return [
            self::TETAP => 'Tetap',
            self::TIDAK_TETAP => 'Tidak Tetap',
        ];
    }
}
