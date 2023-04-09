<?php

/**
 * Menampilkan nilai persentase
 *
 * return decimal
 */

function persen(int $pembilang = 0, int $penyebut = 0, int $desimal = 2, string $pemisah = ',')
{
    $hasil = ($penyebut == 0) ? 0 : $pembilang / $penyebut * 100;

    return number_format($hasil, $desimal, $pemisah) . '%';
}
