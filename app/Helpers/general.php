<?php

use App\Models\Config;
use App\Models\SettingAplikasi;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

if (! function_exists('openkab_versi')) {
    /**
     * OpenKab database gabungan versi.
     */
    function openkab_versi()
    {
        return 'v2401.0.0';
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

function ambilBerkas($pathBerkas, $tampil = true)
{
    if (! file_exists($pathBerkas)) {
        $pathBerkas = public_path('assets/img/opensid_logo.png');
    }

    // Kalau $tampil, tampilkan secara inline.
    if ($tampil) {
        $pathinfo = pathinfo($pathBerkas);
        // Set the default MIME type to send
        switch ($pathinfo['extension']) {
            case 'gif':
                $mime = 'image/gif';
                break;

            case 'png':
                $mime = 'image/png';
                break;

            case 'jpeg':
                $mime = 'image/jpeg';
                break;

            case 'jpg':
                $mime = 'image/jpeg';
                break;

            case 'svg':
                $mime = 'image/svg+xml';
                break;

            case 'pdf':
                $mime = 'application/pdf';
                break;

            default:
                $mime = 'application/octet-stream';
                break;
        }

        // Generate the server headers
        header('Content-Type: '.$mime);
        header('Content-Disposition: inline; filename="'.Str::random(10).'"');
        header('Expires: 0');
        header('Content-Transfer-Encoding: binary');
        header('Content-Length: '.filesize($pathBerkas));
        header('Cache-Control: private, no-transform, no-store, must-revalidate');

        return readfile($pathBerkas);
    }

    response()->download(storage_path($pathBerkas));
}

if (! function_exists('default_favicon')) {
    /**
     * OpenKab database gabungan versi.
     */
    function default_favicon($favicon)
    {
        $path = public_path('favicons');
        if (! file_exists($path)) {
            mkdir($path, 0755, true);
            $pathFavicon = public_path('favicons/'.$favicon);
            if (! file_exists($pathFavicon)) {
                $filePath = public_path('assets/img/opensid_logo.png');
                Image::make($filePath)->resize(96, 96)->save($pathFavicon, '100', 'png');
            }
        }
    }
}

if (! function_exists('date_from_format')) {
    /**
     * OpenKab database gabungan versi.
     */
    function date_from_format($value, $format = null)
    {
        \Log::error($value);

        return Carbon::createFromFormat($format ?? config('app.format.date'), $value);
    }
}

if (! function_exists('getTitle')) {
    /**
     * OpenKab database gabungan versi.
     */
    function getTitle()
    {
        return 'ini judul';
    }
}

if (! function_exists('getDescription')) {
    /**
     * OpenKab database gabungan versi.
     */
    function getDescription()
    {
        return 'ini deskripsi';
    }
}

if (! function_exists('generateMenu')) {
    function generateMenu($tree, $parentId = null)
    {
        $result = '';
        foreach ($tree as $item) {
            if ($item->children->count() > 0) {
                $classLink = empty($parentId) ? 'nav-link' : 'dropdown-item';
                $result .= "
                <li class='nav-item dropdown'>
                    <a class='{$classLink} dropdown-toggle' role='button' href='#' data-bs-toggle='dropdown'>
                    {$item->text}
                    </a>
                    <ul class='dropdown-menu'>";
                $result .= generateMenu($item->children, $item->id);
                $result .= '
                    </ul>
                </li>';
            } else {
                if ($parentId) {
                    $result .= "<li><a class='dropdown-item' href='{$item->href}'>{$item->text}</a></li>";
                } else {
                    $result .= "<li class='nav-item'><a href='{$item->href}' class='nav-link'>{$item->text}</a></li>";
                }
            }
        }

        return $result;
    }
}

if (! function_exists('angka_lokal')) {
    /**
     * Menampilkan nilai persentase.
     *
     * return decimal
     */
    function angka_lokal($angka, $decimal = 0)
    {
        return number_format($angka, $decimal, ',', '.');
    }
}
