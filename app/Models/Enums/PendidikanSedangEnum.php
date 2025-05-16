<?php

namespace App\Models\Enums;

class PendidikanSedangEnum extends BaseEnum
{
    public const BELUM_MASUK_TK_KELOMPOK_BERMAIN = 1;

    public const SEDANG_TK_KELOMPOK_BERMAIN = 2;

    public const TIDAK_PERNAH_SEKOLAH = 3;

    public const SEDANG_SD_SEDERAJAT = 4;

    public const TIDAK_TAMAT_SD_SEDERAJAT = 5;

    public const SEDANG_SLTP_SEDERAJAT = 6;

    public const TIDAK_TAMAT_SLTP_SEDERAJAT = 19;

    public const SEDANG_SLTA_SEDERAJAT = 7;

    public const TIDAK_TAMAT_SLTA_SEDERAJAT = 20;

    public const SEDANG_D_1_SEDERAJAT = 8;

    public const SEDANG_D_2_SEDERAJAT = 9;

    public const SEDANG_D_3_SEDERAJAT = 10;

    public const SEDANG_S_1_SEDERAJAT = 11;

    public const SEDANG_S_2_SEDERAJAT = 12;

    public const SEDANG_S_3_SEDERAJAT = 13;

    public const SEDANG_SLB_A_SEDERAJAT = 14;

    public const SEDANG_SLB_B_SEDERAJAT = 15;

    public const SEDANG_SLB_C_SEDERAJAT = 16;

    public const TIDAK_DAPAT_MEMBACA_DAN_MENULIS_HURUF_LATIN_ARAB = 17;

    public const TIDAK_SEDANG_SEKOLAH = 18;

    /**
     * Override method all().
     */
    public static function all(): array
    {
        return [
            self::BELUM_MASUK_TK_KELOMPOK_BERMAIN => 'BELUM MASUK TK/KELOMPOK BERMAIN',
            self::SEDANG_TK_KELOMPOK_BERMAIN => 'SEDANG TK/KELOMPOK BERMAIN',
            self::TIDAK_PERNAH_SEKOLAH => 'TIDAK PERNAH SEKOLAH',
            self::SEDANG_SD_SEDERAJAT => 'SEDANG SD/SEDERAJAT',
            self::TIDAK_TAMAT_SD_SEDERAJAT => 'TIDAK TAMAT SD/SEDERAJAT',
            self::SEDANG_SLTP_SEDERAJAT => 'SEDANG SLTP/SEDERAJAT',
            self::TIDAK_TAMAT_SLTP_SEDERAJAT => 'TIDAK TAMAT SLTP/SEDERAJAT',
            self::SEDANG_SLTA_SEDERAJAT => 'SEDANG SLTA/SEDERAJAT',
            self::TIDAK_TAMAT_SLTA_SEDERAJAT => 'TIDAK TAMAT SLTA/SEDERAJAT',
            self::SEDANG_D_1_SEDERAJAT => 'SEDANG  D-1/SEDERAJAT',
            self::SEDANG_D_2_SEDERAJAT => 'SEDANG D-2/SEDERAJAT',
            self::SEDANG_D_3_SEDERAJAT => 'SEDANG D-3/SEDERAJAT',
            self::SEDANG_S_1_SEDERAJAT => 'SEDANG  S-1/SEDERAJAT',
            self::SEDANG_S_2_SEDERAJAT => 'SEDANG S-2/SEDERAJAT',
            self::SEDANG_S_3_SEDERAJAT => 'SEDANG S-3/SEDERAJAT',
            self::SEDANG_SLB_A_SEDERAJAT => 'SEDANG SLB A/SEDERAJAT',
            self::SEDANG_SLB_B_SEDERAJAT => 'SEDANG SLB B/SEDERAJAT',
            self::SEDANG_SLB_C_SEDERAJAT => 'SEDANG SLB C/SEDERAJAT',
            self::TIDAK_DAPAT_MEMBACA_DAN_MENULIS_HURUF_LATIN_ARAB => 'TIDAK DAPAT MEMBACA DAN MENULIS HURUF LATIN/ARAB',
            self::TIDAK_SEDANG_SEKOLAH => 'TIDAK SEDANG SEKOLAH',
        ];
    }
}
