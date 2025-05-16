<?php

namespace App\Models\Enums;

class CovidEnum extends BaseEnum
{
    public const KASUS_SUSPEK = 1;

    public const KASUS_PROBABLE = 2;

    public const KASUS_KONFIRMASI = 3;

    public const KONTAK_ERAT = 4;

    public const PELAKU_PERJALANAN = 5;

    public const DISCARDED = 6;

    public const SELESAI_ISOLASI = 7;

    /**
     * Override method all()
     */
    public static function all(): array
    {
        return [
            self::KASUS_SUSPEK => 'Kasus Suspek',
            self::KASUS_PROBABLE => 'Kasus Probable',
            self::KASUS_KONFIRMASI => 'Kasus Konfirmasi',
            self::KONTAK_ERAT => 'Kontak Erat',
            self::PELAKU_PERJALANAN => 'Pelaku Perjalanan',
            self::DISCARDED => 'Discarded',
            self::SELESAI_ISOLASI => 'Selesai Isolasi',
        ];
    }
}
