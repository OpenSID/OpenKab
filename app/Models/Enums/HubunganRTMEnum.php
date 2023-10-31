<?php

namespace App\Models\Enums;

use BenSampo\Enum\Enum;

class HubunganRTMEnum extends Enum
{
    public const KEPALA_RUMAH_TANGGA = 1;

    public const ANGGOTA = 2;

    /**
     * Override method all().
     */
    public static function all(): array
    {
        return [
            self::KEPALA_RUMAH_TANGGA => 'Kepala Rumah Tangga',
            self::ANGGOTA => 'Anggota',
        ];
    }
}
