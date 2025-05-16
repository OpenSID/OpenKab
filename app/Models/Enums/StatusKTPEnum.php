<?php
namespace App\Models\Enums;


class StatusKTPEnum extends BaseEnum
{
    public const BELUM_REKAM            = 2;
    public const SUDAH_REKAM            = 3;
    public const CARD_PRINTED           = 4;
    public const PRINT_READY_RECORD     = 5;
    public const CARD_SHIPPED           = 6;
    public const SENT_FOR_CARD_PRINTING = 7;
    public const CARD_ISSUED            = 8;
    public const BELUM_WAJIB            = 1;

    /**
     * Override method all()
     */
    public static function all(): array
    {
        return [
            self::BELUM_REKAM            => 'BELUM REKAM',
            self::SUDAH_REKAM            => 'SUDAH REKAM',
            self::CARD_PRINTED           => 'CARD PRINTED',
            self::PRINT_READY_RECORD     => 'PRINT READY RECORD',
            self::CARD_SHIPPED           => 'CARD SHIPPED',
            self::SENT_FOR_CARD_PRINTING => 'SENT FOR CARD PRINTING',
            self::CARD_ISSUED            => 'CARD ISSUED',
            self::BELUM_WAJIB            => 'BELUM WAJIB',
        ];
    }
}
