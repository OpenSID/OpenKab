<?php

use App\Models\Config;
use App\Models\SettingAplikasi;

if (! function_exists('openkab_versi')) {
    /**
     * OpenKab database gabungan versi.
     */
    function openkab_versi()
    {
        return 'v2304.0.0';
    }
}

if (! function_exists('persen')) {
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

if (! function_exists('bulan')) {
    /**
     * Nama bulan dalam bahasa Indonesia.
     */
    function bulan(int $bulan): string
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

if (! function_exists('url_title')) {
    /**
     * Create URL Title.
     *
     * Takes a "title" string as input and creates a
     * human-friendly URL string with a "separator" string
     * as the word separator.
     *
     * @todo	Remove old 'dash' and 'underscore' usage in 3.1+.
     *
     * @param string $str       Input string
     * @param string $separator Word separator
     *                          (usually '-' or '_')
     * @param bool   $lowercase Whether to transform the output string to lowercase
     *
     * @return string
     */
    function url_title($str, $separator = '-', $lowercase = false)
    {
        if ($separator === 'dash') {
            $separator = '-';
        } elseif ($separator === 'underscore') {
            $separator = '_';
        }

        $q_separator = preg_quote($separator, '#');

        $trans = [
            '&.+?;' => '',
            '[^\w\d _-]' => '',
            '\s+' => $separator,
            '('.$q_separator.')+' => $separator,
        ];

        $str = strip_tags($str);
        foreach ($trans as $key => $val) {
            $str = preg_replace('#'.$key.'#i', $val, $str);
        }

        if ($lowercase === true) {
            $str = strtolower($str);
        }

        return trim(trim($str, $separator));
    }
}
