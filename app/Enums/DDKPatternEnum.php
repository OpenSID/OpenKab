<?php

namespace App\Enums;

// NOTE : Digunakan untuk replace data pilihan di RTF
class DDKPatternEnum
{
    public const KODE_TEXT = 't[*]';

    public const KODE_VALUE = 'v[*]';

    public const SUMBER_AIR_MINUM_BAIK = 'v[*]1';

    public const SUMBER_AIR_MINUM_BERASA = 'v[*]2';

    public const SUMBER_AIR_MINUM_BERWARNA = 'v[*]3';

    public const SUMBER_AIR_MINUM_BERBAU = 'v[*]4';

    public const KEPEMILIKAN_LAHAN_KURANG_05 = 'v[*]1';

    public const KEPEMILIKAN_LAHAN_ANTARA_05_1 = 'v[*]2';

    public const KEPEMILIKAN_LAHAN_LEBIH_1 = 'v[*]3';

    public const KEPEMILIKAN_LAHAN_TIDAK_MEMILIKI = 'v[*]4';

    public const PRODUKSI_TAHUN_INI_JUMLAH_POHON = 'prod_j[*]';

    public const PRODUKSI_TAHUN_INI_LUAS_PANEN = 'prod_l[*]';

    public const PRODUKSI_TAHUN_INI_PRODUKSI = 'prod_n[*]';

    public const PRODUKSI_TAHUN_INI_SATUAN = 'prod_s[*]';

    public const PRODUKSI_TAHUN_INI_PEMASARAN_HASIL = 'prod_p[*]';

    public const PRODUKSI_BAHAN_GALIAN_PRODUKSI = 'bg_n[*]';

    public const PRODUKSI_BAHAN_GALIAN_MILIK_ADAT = 'bg_m[*]';

    public const PRODUKSI_BAHAN_GALIAN_PERORANGAN = 'bg_o[*]';

    public const PRODUKSI_BAHAN_GALIAN_PEMASARAN_HASIL = 'bg_p[*]';
}
