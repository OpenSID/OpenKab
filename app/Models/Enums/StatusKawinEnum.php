<?php
namespace App\Models\Enums;

class StatusKawinEnum extends BaseEnum
{
    public const BELUMKAWIN = 1;
    public const KAWIN      = 2;
    public const CERAIHIDUP = 3;
    public const CERAIMATI  = 4;

    /**
     * Override method all()
     */
    public static function all(): array
    {
        return [
            self::BELUMKAWIN => 'BELUM KAWIN',
            self::KAWIN      => 'KAWIN',
            self::CERAIHIDUP => 'CERAI HIDUP',
            self::CERAIMATI  => 'CERAI MATI',
        ];
    }
}
