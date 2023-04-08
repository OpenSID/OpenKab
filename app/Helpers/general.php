<?php

/**
 * Menampilkan nilai persentase
 *
 * return float
 */

function persen(int $pembilang = 0, int $penyebut = 0, int $desimal = 2, string $pemisah = ',')
{
    $hasil = 0;

    if ($pembilang > 0 && $penyebut > 0) {
        $hasil = $pembilang / $penyebut * 100;
    }

    return (float) number_format($hasil, $desimal, $pemisah) . '%';
}
