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
        return 'v2410.0.0';
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
        Log::error($value);

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

if (! function_exists('tgl_indo')) {
    function date_indo($tgl): string
    {
        $ubah = gmdate($tgl, time() + 60 * 60 * 8);
        $pecah = explode('-', $ubah);
        $tanggal = $pecah[2];
        $bulan = bulan($pecah[1]);
        $tahun = $pecah[0];

        return $tanggal.' '.$bulan.' '.$tahun;
    }
}

if (! function_exists('bulan')) {
    function bulan($bln)
    {
        switch ($bln) {
            case 1:
                return 'Januari';

            case 2:
                return 'Februari';

            case 3:
                return 'Maret';

            case 04:
                return 'April';

            case 5:
                return 'Mei';

            case 6:
                return 'Juni';

            case 7:
                return 'Juli';

            case 8:
                return 'Agustus';

            case 9:
                return 'September';

            case 10:
                return 'Oktober';

            case 11:
                return 'November';

            case 12:
                return 'Desember';
        }
    }
}

if (! function_exists('bulan2')) {
    function bulan2($bln)
    {
        switch ($bln) {
            case 1:
                return 'Januari';

            case 2:
                return 'Februari';

            case 3:
                return 'Maret';

            case 04:
                return 'April';

            case 5:
                return 'Mei';

            case 6:
                return 'Juni';

            case 7:
                return 'Juli';

            case 8:
                return 'Agustus';

            case 9:
                return 'September';

            case 10:
                return 'Oktober';

            case 11:
                return 'November';

            case 12:
                return 'Desember';
        }
    }
}

//Format Shortdate
if (! function_exists('shortdate_indo')) {
    function shortdate_indo($tgl): string
    {
        $ubah = gmdate($tgl, time() + 60 * 60 * 8);
        $pecah = explode('-', $ubah);
        $tanggal = $pecah[2];
        $bulan = short_bulan($pecah[1]);
        $tahun = $pecah[0];

        return $tanggal.'/'.$bulan.'/'.$tahun;
    }
}

if (! function_exists('short_bulan')) {
    function short_bulan($bln)
    {
        switch ($bln) {
            case 1:
                return '01';

            case 2:
                return '02';

            case 3:
                return '03';

            case 4:
                return '04';

            case 5:
                return '05';

            case 6:
                return '06';

            case 7:
                return '07';

            case 8:
                return '08';

            case 9:
                return '09';

            case 10:
                return '10';

            case 11:
                return '11';

            case 12:
                return '12';
        }
    }
}

//Format Medium date
if (! function_exists('mediumdate_indo')) {
    function mediumdate_indo($tgl): string
    {
        $ubah = gmdate($tgl, time() + 60 * 60 * 8);
        $pecah = explode('-', $ubah);
        $tanggal = $pecah[2];
        $bulan = medium_bulan($pecah[1]);
        $tahun = $pecah[0];

        return $tanggal.'-'.$bulan.'-'.$tahun;
    }
}

if (! function_exists('medium_bulan')) {
    function medium_bulan($bln)
    {
        switch ($bln) {
            case 1:
                return 'Jan';

            case 2:
                return 'Feb';

            case 3:
                return 'Mar';

            case 4:
                return 'Apr';

            case 5:
                return 'Mei';

            case 6:
                return 'Jun';

            case 7:
                return 'Jul';

            case 8:
                return 'Ags';

            case 9:
                return 'Sep';

            case 10:
                return 'Okt';

            case 11:
                return 'Nov';

            case 12:
                return 'Des';
        }
    }
}

//Long date indo Format
if (! function_exists('longdate_indo')) {
    function longdate_indo($tanggal): string
    {
        $ubah = gmdate($tanggal, time() + 60 * 60 * 8);
        $pecah = explode('-', $ubah);
        $tgl = $pecah[2];
        $bln = $pecah[1];
        $thn = $pecah[0];
        $bulan = bulan($pecah[1]);

        $nama = date('l', mktime(0, 0, 0, $bln, $tgl, $thn));
        $nama_hari = '';
        if ($nama == 'Sunday') {
            $nama_hari = 'Minggu';
        } elseif ($nama == 'Monday') {
            $nama_hari = 'Senin';
        } elseif ($nama == 'Tuesday') {
            $nama_hari = 'Selasa';
        } elseif ($nama == 'Wednesday') {
            $nama_hari = 'Rabu';
        } elseif ($nama == 'Thursday') {
            $nama_hari = 'Kamis';
        } elseif ($nama == 'Friday') {
            $nama_hari = 'Jumat';
        } elseif ($nama == 'Saturday') {
            $nama_hari = 'Sabtu';
        }

        return $nama_hari.','.$tgl.' '.$bulan.' '.$thn;
    }
}

if (! function_exists('bulan_array')) {
    function bulan_array()
    {
        return [
            [
                'urut' => 1,
                'nama_pendek' => medium_bulan(1),
                'nama_panjang' => bulan(1),
            ],
            [
                'urut' => 2,
                'nama_pendek' => medium_bulan(2),
                'nama_panjang' => bulan(2),
            ],
            [
                'urut' => 3,
                'nama_pendek' => medium_bulan(3),
                'nama_panjang' => bulan(3),
            ],
            [
                'urut' => 4,
                'nama_pendek' => medium_bulan(4),
                'nama_panjang' => bulan(4),
            ],
            [
                'urut' => 5,
                'nama_pendek' => medium_bulan(5),
                'nama_panjang' => bulan(5),
            ],
            [
                'urut' => 6,
                'nama_pendek' => medium_bulan(6),
                'nama_panjang' => bulan(6),
            ],
            [
                'urut' => 7,
                'nama_pendek' => medium_bulan(7),
                'nama_panjang' => bulan(7),
            ],
            [
                'urut' => 8,
                'nama_pendek' => medium_bulan(8),
                'nama_panjang' => bulan(8),
            ],
            [
                'urut' => 9,
                'nama_pendek' => medium_bulan(9),
                'nama_panjang' => bulan(9),
            ],
            [
                'urut' => 10,
                'nama_pendek' => medium_bulan(10),
                'nama_panjang' => bulan(10),
            ],
            [
                'urut' => 11,
                'nama_pendek' => medium_bulan(11),
                'nama_panjang' => bulan(11),
            ],
            [
                'urut' => 12,
                'nama_pendek' => medium_bulan(12),
                'nama_panjang' => bulan(12),
            ],
        ];
    }
}

if (! function_exists('bulan2_array')) {
    function bulan2_array()
    {
        return [
            [
                'urut' => 1,
                'nama_panjang' => 'Januari',
            ],
            [
                'urut' => 2,
                'nama_panjang' => 'Februari',
            ],
            [
                'urut' => 3,
                'nama_panjang' => 'Maret',
            ],
            [
                'urut' => 4,
                'nama_panjang' => 'April',
            ],
            [
                'urut' => 5,
                'nama_panjang' => 'Mei',
            ],
            [
                'urut' => 6,
                'nama_panjang' => 'Juni',
            ],
            [
                'urut' => 7,
                'nama_panjang' => 'Juli',
            ],
            [
                'urut' => 8,
                'nama_panjang' => 'Agustus',
            ],
            [
                'urut' => 9,
                'nama_panjang' => 'September',
            ],
            [
                'urut' => 10,
                'nama_panjang' => 'Oktober',
            ],
            [
                'urut' => 11,
                'nama_panjang' => 'November',
            ],
            [
                'urut' => 12,
                'nama_panjang' => 'Desember',
            ],
        ];
    }
}

// die(json_encode(bulan_array()[1]));

if (! function_exists('kuartal')) {
    function kuartal()
    {
        return [
            [
                'ke' => 1,
                'bulan' => bulan_array()[0]['nama_panjang'].' - '.bulan_array()[2]['nama_panjang'],
            ],
            [
                'ke' => 2,
                'bulan' => bulan_array()[3]['nama_panjang'].' - '.bulan_array()[5]['nama_panjang'],
            ],
            [
                'ke' => 3,
                'bulan' => bulan_array()[6]['nama_panjang'].' - '.bulan_array()[8]['nama_panjang'],
            ],
            [
                'ke' => 4,
                'bulan' => bulan_array()[9]['nama_panjang'].' - '.bulan_array()[11]['nama_panjang'],
            ],
        ];
    }
}

if (! function_exists('kuartal2')) {
    function kuartal2()
    {
        return [
            [
                'ke' => 1,
                'bulan' => bulan2_array()[0]['nama_panjang'].' - '.bulan2_array()[2]['nama_panjang'],
            ],
            [
                'ke' => 2,
                'bulan' => bulan2_array()[3]['nama_panjang'].' - '.bulan2_array()[5]['nama_panjang'],
            ],
            [
                'ke' => 3,
                'bulan' => bulan2_array()[6]['nama_panjang'].' - '.bulan2_array()[8]['nama_panjang'],
            ],
            [
                'ke' => 4,
                'bulan' => bulan2_array()[9]['nama_panjang'].' - '.bulan2_array()[11]['nama_panjang'],
            ],
        ];
    }
}

if (! function_exists('get_kuartal')) {
    function get_kuartal($kuartal = null)
    {
        if ($kuartal == null || $kuartal < 0 || $kuartal > 4) {
            return [
                'ke' => 'undefined',
                'bulan' => 'undefined',
            ];
        }

        return kuartal2()[$kuartal - 1];
    }
}

function persen3($number, $total, $precision = 2)
{
    // Can't divide by zero so let's catch that early.
    if ($total == 0) {
        return 0;
    }

    return round(($number / $total) * 100, $precision) . '%';
}