<?php

use App\Models\Config;
use App\Models\SettingAplikasi;

/**
 * Menampilkan nilai persentase.
 *
 * return decimal
 */
function persen(int $pembilang = 0, int $penyebut = 0, int $desimal = 2, string $pemisah = ',')
{
    $hasil = ($penyebut == 0) ? 0 : $pembilang / $penyebut * 100;

    return number_format($hasil, $desimal, $pemisah).'%';
}

// setting('sebutan_desa');
if (! function_exists('setting')) {
    function setting($config_id = null, $params = null)
    {
        if ($params && $config_id) {
            $getSetting = SettingAplikasi::where('config_id', $config_id)->where('key', $params)->first();
            if ($getSetting) {
                return $getSetting->value;
            }
        }

        return null;
    }
}

// identitas('nama_desa');
if (! function_exists('identitas')) {
    /**
     * Get identitas desa.
     *
     * @return object|string
     */
    function identitas(int $config_id = null, string $params = null)
    {
        if ($config_id && $params) {
            $config = Config::where('id', $config_id)->first();
            if ($config) {
                return $config->{$params};
            }
        }

        return null;
    }
}

// helper bulan
if (! function_exists('bulan')) {
    function bulan($bulan = null)
    {
        return match ($bulan) {
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
            default => 'Januari',
        };
    }
}
