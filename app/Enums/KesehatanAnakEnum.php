<?php

namespace App\Enums;

class KesehatanAnakEnum
{
    public const NORMAL = 1;

    public const GIZI_KURANG = 2;

    public const GIZI_BURUK = 3;

    public const STUNTING = 4;

    public const TB_SANGAT_PENDEK = 2;

    public const TB_PENDEK = 3;

    public const KATEGORI_STATISTIK = [
        'stunting' => 'Stunting',
    ];

    public const STATUS_GIZI_ANAK = [
        ['id' => self::NORMAL, 'simbol' => 'N', 'nama' => 'Sehat / Normal (N)'],
        ['id' => self::GIZI_KURANG, 'simbol' => 'GK', 'nama' => 'Gizi Kurang (GK)'],
        ['id' => self::GIZI_BURUK, 'simbol' => 'GB', 'nama' => 'Gizi Buruk (GB)'],
        ['id' => self::STUNTING, 'simbol' => 'S', 'nama' => 'Stunting (S)'],
    ];

    public const STATUS_TIKAR_ANAK = [
        ['id' => 1, 'simbol' => 'TD', 'nama' => 'Tidak Diukur (TD)'],
        ['id' => 2, 'simbol' => 'M', 'nama' => 'Merah (M)'],
        ['id' => 3, 'simbol' => 'K', 'nama' => 'Kuning (K)'],
        ['id' => 4, 'simbol' => 'H', 'nama' => 'Hijau (H)'],
    ];

    public const STATUS_IMUNISASI_CAMPAK = [
        1 => 'Belum',
        2 => 'Sudah',
    ];
}
