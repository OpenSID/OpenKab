<?php

namespace App\Models\Enums;

class GolonganDarahEnum extends BaseEnum
{
    public const A          = 1;
    public const B          = 2;
    public const AB         = 3;
    public const O          = 4;
    public const A_PLUS     = 5;
    public const A_MINUS    = 6;
    public const B_PLUS     = 7;
    public const B_MINUS    = 8;
    public const AB_PLUS    = 9;
    public const AB_MINUS   = 10;
    public const O_PLUS     = 11;
    public const O_MINUS    = 12;
    public const TIDAK_TAHU = 13;

    /**
     * Override method all()
     */
    public static function all(): array
    {
        return [
            self::A          => 'A',
            self::B          => 'B',
            self::AB         => 'AB',
            self::O          => 'O',
            self::A_PLUS     => 'A+',
            self::A_MINUS    => 'A-',
            self::B_PLUS     => 'B+',
            self::B_MINUS    => 'B-',
            self::AB_PLUS    => 'AB+',
            self::AB_MINUS   => 'AB-',
            self::O_PLUS     => 'O+',
            self::O_MINUS    => 'O-',
            self::TIDAK_TAHU => 'TIDAK TAHU',
        ];
    }
}
