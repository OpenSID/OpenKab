<?php
namespace App\Models\Enums;


class CaraKBEnum extends BaseEnum
{
    public const PIL                = 1;
    public const IUD                = 2;
    public const SUNTIK             = 3;
    public const KONDOM             = 4;
    public const SUSUK_KB           = 5;
    public const STERILISASI_WANITA = 6;
    public const STERILISASI_PRIA   = 7;
    public const LAINNYA            = 99;
    public const TIDAK_MENGGUNAKAN  = 100;

    /**
     * Override method all()
     */
    public static function all(): array
    {
        return [
            self::PIL                => 'Pil',
            self::IUD                => 'IUD',
            self::SUNTIK             => 'Suntik',
            self::KONDOM             => 'Kondom',
            self::SUSUK_KB           => 'Susuk KB',
            self::STERILISASI_WANITA => 'Sterilisasi Wanita',
            self::STERILISASI_PRIA   => 'Sterilisasi Pria',
            self::LAINNYA            => 'Lainnya',
            self::TIDAK_MENGGUNAKAN  => 'Tidak Menggunakan',
        ];
    }
}
