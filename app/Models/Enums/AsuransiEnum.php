<?php

namespace App\Models\Enums;

class AsuransiEnum extends BaseEnum
{
    public const TIDAK_BELUM_PUNYA               = 1;
    public const BPJS_PENERIMA_BANTUAN_IURAN     = 2;
    public const BPJS_NON_PENERIMA_BANTUAN_IURAN = 3;
    public const BPJS_BANTUAN_DAERAH             = 4;
    public const ASURANSI_LAINNYA                = 99;

    /**
     * Override method all()
     */
    public static function all(): array
    {
        return [
            self::TIDAK_BELUM_PUNYA               => 'Tidak/Belum Punya',
            self::BPJS_PENERIMA_BANTUAN_IURAN     => 'BPJS Penerima Bantuan Iuran',
            self::BPJS_NON_PENERIMA_BANTUAN_IURAN => 'BPJS Non Penerima Bantuan Iuran',
            self::BPJS_BANTUAN_DAERAH             => 'BPJS Bantuan Daerah',
            self::ASURANSI_LAINNYA                => 'Asuransi Lainnya',
        ];
    }
}
