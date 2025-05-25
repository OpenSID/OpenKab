<?php

namespace App\Models\Enums;

class WargaNegaraEnum extends BaseEnum
{
    public const WNI = 1;

    public const WNA = 2;

    public const DUAKEWARGANEGARAAN = 3;

    /**
     * Override method all().
     */
    public static function all(): array
    {
        return [
            self::WNI => 'WNI',
            self::WNA => 'WNA',
            self::DUAKEWARGANEGARAAN => 'DUA KEWARGANEGARAAN',
        ];
    }
}
