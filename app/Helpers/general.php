<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

define('SASARAN', serialize([
    '1' => 'Penduduk',
    '2' => 'Keluarga / KK',
]));

define('STATUS_SUPLEMEN', serialize([
    '1' => 'Aktif',
    '0' => 'Tidak Aktif',
]));

define('ATTRIBUTES', serialize([
    'text' => 'Input Teks',
    'number' => 'Input Angka',
    'email' => 'Input Email',
    'url' => 'Input Url',
    'date' => 'Input Tanggal',
    'time' => 'Input Jam',
    'textarea' => 'Text Area',
    'select-manual' => 'Pilihan (Kustom)',
]));

if (! function_exists('openkab_versi')) {
    /**
     * OpenKab database gabungan versi.
     */
    function openkab_versi()
    {
        return 'v2510.0.0';
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

if (! function_exists('generateMenuPresisi')) {
    function generateMenuPresisi($tree, $parentId = null)
    {
        $result = '';
        foreach ($tree as $item) {
            // Mengambil ikon jika ada, dan membuat tag HTML ikon
            $icon = isset($item->icon) ? "<i class='{$item->icon}'></i> " : '';

            if ($item->children->count() > 0) {
                $result .= "<div class='parent-dropdown-menu dropdown bg-white pl-3 pr-2'>
                 <button class='parent-menu btn bg-white p-2 dropdown-toggle text-muted rounded-0' type='button'  id='{$item->id}Dropdown' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
                     {$icon}{$item->text}
                 </button>
                 <div class='dropdown-menu' aria-labelledby='{$item->id}Dropdown'>";
                $result .= generateMenuPresisi($item->children, $item->id);
                $result .= '
                    </div>
               </div>';
            } else {
                if ($parentId) {
                    $result .= "<a class='item-menu dropdown-item' href='{$item->href}'>{$icon}{$item->text}</a>";
                } else {
                    $href = $item->href;
                    if ($href != '/presisi' and $href != '/presisi/kesehatan' and $href != '/presisi/geo-spasial') {
                        $href = str_contains($item->href, 'module') ? $item->href : '/'.$item->href;
                    }
                    $result .= "<a type='button' class='item-menu btn bg-white p-2 text-muted' href='{$href}'>{$icon}{$item->text}</a>";
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

if (! function_exists('getAllBulan')) {
    function getAllBulan()
    {
        return [1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'];
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

    return round(($number / $total) * 100, $precision).'%';
}

if (! function_exists('convertMenu')) {
    /**
     * Helper untuk menentukan convert menu agar sesuai dengan editor.
     *
     * @param mixed $value
     * @param mixed $checked
     *
     * @return string
     */
    function convertMenu($menu, $parentId = null, &$idCounter = 1)
    {
        $convertedMenu = [
            'id' => $idCounter++,
            'parent_id' => $parentId,
            'text' => $menu['text'] ?? 'text',
            'href' => $menu['url'] ?? null,
            'icon' => $menu['icon'],
            'permission' => $menu['permission'],
        ];

        if (isset($menu['submenu']) && is_array($menu['submenu'])) {
            $convertedMenu['children'] = [];
            foreach ($menu['submenu'] as $submenu) {
                $convertedMenu['children'][] = convertMenu($submenu, $convertedMenu['id'], $idCounter);
            }
        }

        return $convertedMenu;
    }
}

if (! function_exists('convertDatabaseMenu')) {
    /**
     * Helper untuk menentukan convert menu untuk disimpan di database.
     *
     * @return array
     */
    function convertDatabaseMenu($menu, $parentId = null, &$idCounter = 1)
    {
        $convertedMenu = [
            'id' => $idCounter++,
            'parent_id' => $parentId,
            'text' => $menu['text'] ?? 'text',
            'url' => $menu['href'] ?? null,
            'icon' => $menu['icon'] ?? null,
            'permission' => $menu['permission'] ?? null,
        ];

        if (isset($menu['children']) && is_array($menu['children'])) {
            $convertedMenu['submenu'] = [];
            foreach ($menu['children'] as $submenu) {
                $convertedMenu['submenu'][] = convertDatabaseMenu($submenu, $convertedMenu['id'], $idCounter);
            }
        }

        return $convertedMenu;
    }
}

if (! function_exists('gis_simbols')) {
    function gis_simbols()
    {
        $simbols = [
            ['simbol' => 'aa_bni.png'],
            ['simbol' => 'aa_bri.png'],
            ['simbol' => 'aa_btn.png'],
            ['simbol' => 'aa_btp.png'],
            ['simbol' => 'aa_pajak.png'],
            ['simbol' => 'aa_pdam.png'],
            ['simbol' => 'aa_pgadai.png'],
            ['simbol' => 'aa_pln.png'],
            ['simbol' => 'aa_pmi.png'],
            ['simbol' => 'aa_polisi.png'],
            ['simbol' => 'aa_prtmn.png'],
            ['simbol' => 'aa_pskms.png'],
            ['simbol' => 'aa_ptrns.png'],
            ['simbol' => 'aa_pwbdh.png'],
            ['simbol' => 'aa_pwhnd.png'],
            ['simbol' => 'aa_pwisl.png'],
            ['simbol' => 'aa_pwkhc.png'],
            ['simbol' => 'aa_pwkrs.png'],
            ['simbol' => 'aa_sk.png'],
            ['simbol' => 'aa_skagm.png'],
            ['simbol' => 'aa_skint.png'],
            ['simbol' => 'aa_sksd.png'],
            ['simbol' => 'aa_sksma.png'],
            ['simbol' => 'aa_sksmp.png'],
            ['simbol' => 'aa_sktk.png'],
            ['simbol' => 'aa_tniad.png'],
            ['simbol' => 'aa_tnial.png'],
            ['simbol' => 'aa_tniau.png'],
            ['simbol' => 'accident.png'],
            ['simbol' => 'accident_2.png'],
            ['simbol' => 'administration.png'],
            ['simbol' => 'administration_2.png'],
            ['simbol' => 'aestheticscenter.png'],
            ['simbol' => 'agriculture.png'],
            ['simbol' => 'agriculture2.png'],
            ['simbol' => 'agriculture3.png'],
            ['simbol' => 'agriculture4.png'],
            ['simbol' => 'aircraft-small.png'],
            ['simbol' => 'airplane-sport.png'],
            ['simbol' => 'airplane-tourism.png'],
            ['simbol' => 'airport-apron.png'],
            ['simbol' => 'airport-runway.png'],
            ['simbol' => 'airport-terminal.png'],
            ['simbol' => 'airport.png'],
            ['simbol' => 'airport_2.png'],
            ['simbol' => 'amphitheater-tourism.png'],
            ['simbol' => 'amphitheater.png'],
            ['simbol' => 'ancientmonument.png'],
            ['simbol' => 'ancienttemple.png'],
            ['simbol' => 'ancienttempleruin.png'],
            ['simbol' => 'animals.png'],
            ['simbol' => 'animals_2.png'],
            ['simbol' => 'anniversary.png'],
            ['simbol' => 'apartment.png'],
            ['simbol' => 'apartment_2.png'],
            ['simbol' => 'aquarium.png'],
            ['simbol' => 'arch.png'],
            ['simbol' => 'archery.png'],
            ['simbol' => 'artgallery.png'],
            ['simbol' => 'atm.png'],
            ['simbol' => 'atv.png'],
            ['simbol' => 'audio.png'],
            ['simbol' => 'australianfootball.png'],
            ['simbol' => 'bags.png'],
            ['simbol' => 'bank.png'],
            ['simbol' => 'bankeuro.png'],
            ['simbol' => 'bankpound.png'],
            ['simbol' => 'bank_2.png'],
            ['simbol' => 'bar.png'],
            ['simbol' => 'bar_2.png'],
            ['simbol' => 'baseball.png'],
            ['simbol' => 'basketball.png'],
            ['simbol' => 'baskteball2.png'],
            ['simbol' => 'beach.png'],
            ['simbol' => 'beach_2.png'],
            ['simbol' => 'beautiful.png'],
            ['simbol' => 'beautiful_2.png'],
            ['simbol' => 'bench.png'],
            ['simbol' => 'biblio.png'],
            ['simbol' => 'bicycleparking.png'],
            ['simbol' => 'bigcity.png'],
            ['simbol' => 'billiard.png'],
            ['simbol' => 'bobsleigh.png'],
            ['simbol' => 'bomb.png'],
            ['simbol' => 'bookstore.png'],
            ['simbol' => 'bowling.png'],
            ['simbol' => 'bowling_2.png'],
            ['simbol' => 'boxing.png'],
            ['simbol' => 'bread.png'],
            ['simbol' => 'bread_2.png'],
            ['simbol' => 'bridge.png'],
            ['simbol' => 'bridgemodern.png'],
            ['simbol' => 'bullfight.png'],
            ['simbol' => 'bungalow.png'],
            ['simbol' => 'bus.png'],
            ['simbol' => 'bus_2.png'],
            ['simbol' => 'butcher.png'],
            ['simbol' => 'cabin.png'],
            ['simbol' => 'cablecar.png'],
            ['simbol' => 'camping.png'],
            ['simbol' => 'campingsite.png'],
            ['simbol' => 'camping_2.png'],
            ['simbol' => 'canoe.png'],
            ['simbol' => 'car.png'],
            ['simbol' => 'carrental.png'],
            ['simbol' => 'carrepair.png'],
            ['simbol' => 'carrepair_2.png'],
            ['simbol' => 'carwash.png'],
            ['simbol' => 'car_2.png'],
            ['simbol' => 'casino.png'],
            ['simbol' => 'casino_2.png'],
            ['simbol' => 'castle.png'],
            ['simbol' => 'cathedral.png'],
            ['simbol' => 'cathedral2.png'],
            ['simbol' => 'cave.png'],
            ['simbol' => 'cemetary.png'],
            ['simbol' => 'chapel.png'],
            ['simbol' => 'church.png'],
            ['simbol' => 'church2.png'],
            ['simbol' => 'church_2.png'],
            ['simbol' => 'cinema.png'],
            ['simbol' => 'cinema_2.png'],
            ['simbol' => 'circus.png'],
            ['simbol' => 'citysquare.png'],
            ['simbol' => 'climbing.png'],
            ['simbol' => 'clothes-female.png'],
            ['simbol' => 'clothes-male.png'],
            ['simbol' => 'clothes.png'],
            ['simbol' => 'clothes_2.png'],
            ['simbol' => 'clouds.png'],
            ['simbol' => 'cloudsun.png'],
            ['simbol' => 'cloudsun_2.png'],
            ['simbol' => 'clouds_2.png'],
            ['simbol' => 'club.png'],
            ['simbol' => 'club_2.png'],
            ['simbol' => 'cluster.png'],
            ['simbol' => 'cluster2.png'],
            ['simbol' => 'cluster3.png'],
            ['simbol' => 'cluster4.png'],
            ['simbol' => 'cluster5.png'],
            ['simbol' => 'cocktail.png'],
            ['simbol' => 'coffee.png'],
            ['simbol' => 'coffee_2.png'],
            ['simbol' => 'communitycentre.png'],
            ['simbol' => 'company.png'],
            ['simbol' => 'company_2.png'],
            ['simbol' => 'computer.png'],
            ['simbol' => 'computer_2.png'],
            ['simbol' => 'concessionaire.png'],
            ['simbol' => 'conference.png'],
            ['simbol' => 'construction.png'],
            ['simbol' => 'convenience.png'],
            ['simbol' => 'convent.png'],
            ['simbol' => 'corral.png'],
            ['simbol' => 'country.png'],
            ['simbol' => 'court.png'],
            ['simbol' => 'cricket.png'],
            ['simbol' => 'cross.png'],
            ['simbol' => 'crossingguard.png'],
            ['simbol' => 'cruise.png'],
            ['simbol' => 'currencyexchange.png'],
            ['simbol' => 'customs.png'],
            ['simbol' => 'cycling.png'],
            ['simbol' => 'cyclingfeedarea.png'],
            ['simbol' => 'cyclingmountain1.png'],
            ['simbol' => 'cyclingmountain2.png'],
            ['simbol' => 'cyclingmountain3.png'],
            ['simbol' => 'cyclingmountain4.png'],
            ['simbol' => 'cyclingmountainnotrated.png'],
            ['simbol' => 'cyclingsport.png'],
            ['simbol' => 'cyclingsprint.png'],
            ['simbol' => 'cyclinguncategorized.png'],
            ['simbol' => 'cycling_2.png'],
            ['simbol' => 'dam.png'],
            ['simbol' => 'dancinghall.png'],
            ['simbol' => 'dates.png'],
            ['simbol' => 'dates_2.png'],
            ['simbol' => 'daycare.png'],
            ['simbol' => 'days-dim.png'],
            ['simbol' => 'days-dom.png'],
            ['simbol' => 'days-jeu.png'],
            ['simbol' => 'days-jue.png'],
            ['simbol' => 'days-lun.png'],
            ['simbol' => 'days-mar.png'],
            ['simbol' => 'days-mer.png'],
            ['simbol' => 'days-mie.png'],
            ['simbol' => 'days-qua.png'],
            ['simbol' => 'days-qui.png'],
            ['simbol' => 'days-sab.png'],
            ['simbol' => 'days-sam.png'],
            ['simbol' => 'days-seg.png'],
            ['simbol' => 'days-sex.png'],
            ['simbol' => 'days-ter.png'],
            ['simbol' => 'days-ven.png'],
            ['simbol' => 'days-vie.png'],
            ['simbol' => 'default.png'],
            ['simbol' => 'dentist.png'],
            ['simbol' => 'deptstore.png'],
            ['simbol' => 'disability.png'],
            ['simbol' => 'disability_2.png'],
            ['simbol' => 'disabledparking.png'],
            ['simbol' => 'diving.png'],
            ['simbol' => 'doctor.png'],
            ['simbol' => 'doctor_2.png'],
            ['simbol' => 'dog-leash.png'],
            ['simbol' => 'dog-offleash.png'],
            ['simbol' => 'door.png'],
            ['simbol' => 'down.png'],
            ['simbol' => 'downleft.png'],
            ['simbol' => 'downright.png'],
            ['simbol' => 'downthenleft.png'],
            ['simbol' => 'downthenright.png'],
            ['simbol' => 'drinkingfountain.png'],
            ['simbol' => 'drinkingwater.png'],
            ['simbol' => 'drugs.png'],
            ['simbol' => 'drugs_2.png'],
            ['simbol' => 'elevator.png'],
            ['simbol' => 'embassy.png'],
            ['simbol' => 'emblem-art.png'],
            ['simbol' => 'emblem-photos.png'],
            ['simbol' => 'entrance.png'],
            ['simbol' => 'escalator-down.png'],
            ['simbol' => 'escalator-up.png'],
            ['simbol' => 'exit.png'],
            ['simbol' => 'expert.png'],
            ['simbol' => 'explosion.png'],
            ['simbol' => 'face-devilish.png'],
            ['simbol' => 'face-embarrassed.png'],
            ['simbol' => 'factory.png'],
            ['simbol' => 'factory_2.png'],
            ['simbol' => 'fallingrocks.png'],
            ['simbol' => 'family.png'],
            ['simbol' => 'farm.png'],
            ['simbol' => 'farm_2.png'],
            ['simbol' => 'fastfood.png'],
            ['simbol' => 'fastfood_2.png'],
            ['simbol' => 'festival-itinerant.png'],
            ['simbol' => 'festival.png'],
            ['simbol' => 'findajob.png'],
            ['simbol' => 'findjob.png'],
            ['simbol' => 'findjob_2.png'],
            ['simbol' => 'fire-extinguisher.png'],
            ['simbol' => 'fire.png'],
            ['simbol' => 'firemen.png'],
            ['simbol' => 'firemen_2.png'],
            ['simbol' => 'fireworks.png'],
            ['simbol' => 'firstaid.png'],
            ['simbol' => 'fishing.png'],
            ['simbol' => 'fishingshop.png'],
            ['simbol' => 'fishing_2.png'],
            ['simbol' => 'fitnesscenter.png'],
            ['simbol' => 'fjord.png'],
            ['simbol' => 'flood.png'],
            ['simbol' => 'flowers.png'],
            ['simbol' => 'flowers_2.png'],
            ['simbol' => 'followpath.png'],
            ['simbol' => 'foodtruck.png'],
            ['simbol' => 'forest.png'],
            ['simbol' => 'fortress.png'],
            ['simbol' => 'fossils.png'],
            ['simbol' => 'fountain.png'],
            ['simbol' => 'friday.png'],
            ['simbol' => 'friday_2.png'],
            ['simbol' => 'friends.png'],
            ['simbol' => 'friends_2.png'],
            ['simbol' => 'garden.png'],
            ['simbol' => 'gateswalls.png'],
            ['simbol' => 'gazstation.png'],
            ['simbol' => 'gazstation_2.png'],
            ['simbol' => 'geyser.png'],
            ['simbol' => 'gifts.png'],
            ['simbol' => 'girlfriend.png'],
            ['simbol' => 'girlfriend_2.png'],
            ['simbol' => 'glacier.png'],
            ['simbol' => 'golf.png'],
            ['simbol' => 'golf_2.png'],
            ['simbol' => 'gondola.png'],
            ['simbol' => 'gourmet.png'],
            ['simbol' => 'grocery.png'],
            ['simbol' => 'gun.png'],
            ['simbol' => 'gym.png'],
            ['simbol' => 'hairsalon.png'],
            ['simbol' => 'handball.png'],
            ['simbol' => 'hanggliding.png'],
            ['simbol' => 'hats.png'],
            ['simbol' => 'headstone.png'],
            ['simbol' => 'headstonejewish.png'],
            ['simbol' => 'helicopter.png'],
            ['simbol' => 'highway.png'],
            ['simbol' => 'highway_2.png'],
            ['simbol' => 'hiking-tourism.png'],
            ['simbol' => 'hiking.png'],
            ['simbol' => 'hiking_2.png'],
            ['simbol' => 'historicalquarter.png'],
            ['simbol' => 'home.png'],
            ['simbol' => 'home_2.png'],
            ['simbol' => 'horseriding.png'],
            ['simbol' => 'horseriding_2.png'],
            ['simbol' => 'hospital.png'],
            ['simbol' => 'hospital_2.png'],
            ['simbol' => 'hostel.png'],
            ['simbol' => 'hotairballoon.png'],
            ['simbol' => 'hotel.png'],
            ['simbol' => 'hotel1star.png'],
            ['simbol' => 'hotel2stars.png'],
            ['simbol' => 'hotel3stars.png'],
            ['simbol' => 'hotel4stars.png'],
            ['simbol' => 'hotel5stars.png'],
            ['simbol' => 'hotel_2.png'],
            ['simbol' => 'house.png'],
            ['simbol' => 'hunting.png'],
            ['simbol' => 'icecream.png'],
            ['simbol' => 'icehockey.png'],
            ['simbol' => 'iceskating.png'],
            ['simbol' => 'im-user.png'],
            ['simbol' => 'index.html'],
            ['simbol' => 'info.png'],
            ['simbol' => 'info_2.png'],
            ['simbol' => 'jewelry.png'],
            ['simbol' => 'jewishquarter.png'],
            ['simbol' => 'jogging.png'],
            ['simbol' => 'judo.png'],
            ['simbol' => 'justice.png'],
            ['simbol' => 'justice_2.png'],
            ['simbol' => 'karate.png'],
            ['simbol' => 'karting.png'],
            ['simbol' => 'kayak.png'],
            ['simbol' => 'laboratory.png'],
            ['simbol' => 'lake.png'],
            ['simbol' => 'laundromat.png'],
            ['simbol' => 'left.png'],
            ['simbol' => 'leftthendown.png'],
            ['simbol' => 'leftthenup.png'],
            ['simbol' => 'library.png'],
            ['simbol' => 'library_2.png'],
            ['simbol' => 'lighthouse.png'],
            ['simbol' => 'liquor.png'],
            ['simbol' => 'lock.png'],
            ['simbol' => 'lockerrental.png'],
            ['simbol' => 'magicshow.png'],
            ['simbol' => 'mainroad.png'],
            ['simbol' => 'massage.png'],
            ['simbol' => 'military.png'],
            ['simbol' => 'military_2.png'],
            ['simbol' => 'mine.png'],
            ['simbol' => 'mobilephonetower.png'],
            ['simbol' => 'modernmonument.png'],
            ['simbol' => 'moderntower.png'],
            ['simbol' => 'monastery.png'],
            ['simbol' => 'monday.png'],
            ['simbol' => 'monday_2.png'],
            ['simbol' => 'monument.png'],
            ['simbol' => 'mosque.png'],
            ['simbol' => 'motorbike.png'],
            ['simbol' => 'motorcycle.png'],
            ['simbol' => 'movierental.png'],
            ['simbol' => 'museum-archeological.png'],
            ['simbol' => 'museum-art.png'],
            ['simbol' => 'museum-crafts.png'],
            ['simbol' => 'museum-historical.png'],
            ['simbol' => 'museum-naval.png'],
            ['simbol' => 'museum-science.png'],
            ['simbol' => 'museum-war.png'],
            ['simbol' => 'museum.png'],
            ['simbol' => 'museum_2.png'],
            ['simbol' => 'music-classical.png'],
            ['simbol' => 'music-hiphop.png'],
            ['simbol' => 'music-live.png'],
            ['simbol' => 'music-rock.png'],
            ['simbol' => 'music.png'],
            ['simbol' => 'music_2.png'],
            ['simbol' => 'nanny.png'],
            ['simbol' => 'newsagent.png'],
            ['simbol' => 'nordicski.png'],
            ['simbol' => 'nursery.png'],
            ['simbol' => 'observatory.png'],
            ['simbol' => 'oilpumpjack.png'],
            ['simbol' => 'olympicsite.png'],
            ['simbol' => 'ophthalmologist.png'],
            ['simbol' => 'pagoda.png'],
            ['simbol' => 'paint.png'],
            ['simbol' => 'palace.png'],
            ['simbol' => 'panoramic.png'],
            ['simbol' => 'panoramic180.png'],
            ['simbol' => 'park-urban.png'],
            ['simbol' => 'park.png'],
            ['simbol' => 'parkandride.png'],
            ['simbol' => 'parking.png'],
            ['simbol' => 'parking_2.png'],
            ['simbol' => 'park_2.png'],
            ['simbol' => 'party.png'],
            ['simbol' => 'patisserie.png'],
            ['simbol' => 'pedestriancrossing.png'],
            ['simbol' => 'pend.png'],
            ['simbol' => 'pens.png'],
            ['simbol' => 'perfumery.png'],
            ['simbol' => 'personal.png'],
            ['simbol' => 'personalwatercraft.png'],
            ['simbol' => 'petroglyphs.png'],
            ['simbol' => 'pets.png'],
            ['simbol' => 'phones.png'],
            ['simbol' => 'photo.png'],
            ['simbol' => 'photodown.png'],
            ['simbol' => 'photodownleft.png'],
            ['simbol' => 'photodownright.png'],
            ['simbol' => 'photography.png'],
            ['simbol' => 'photoleft.png'],
            ['simbol' => 'photoright.png'],
            ['simbol' => 'photoup.png'],
            ['simbol' => 'photoupleft.png'],
            ['simbol' => 'photoupright.png'],
            ['simbol' => 'picnic.png'],
            ['simbol' => 'pizza.png'],
            ['simbol' => 'pizza_2.png'],
            ['simbol' => 'places-unvisited.png'],
            ['simbol' => 'places-visited.png'],
            ['simbol' => 'planecrash.png'],
            ['simbol' => 'playground.png'],
            ['simbol' => 'playground_2.png'],
            ['simbol' => 'poker.png'],
            ['simbol' => 'poker_2.png'],
            ['simbol' => 'police.png'],
            ['simbol' => 'police2.png'],
            ['simbol' => 'police_2.png'],
            ['simbol' => 'pool-indoor.png'],
            ['simbol' => 'pool.png'],
            ['simbol' => 'pool_2.png'],
            ['simbol' => 'port.png'],
            ['simbol' => 'port_2.png'],
            ['simbol' => 'postal.png'],
            ['simbol' => 'postal_2.png'],
            ['simbol' => 'powerlinepole.png'],
            ['simbol' => 'powerplant.png'],
            ['simbol' => 'powersubstation.png'],
            ['simbol' => 'prison.png'],
            ['simbol' => 'protectedart.png'],
            ['simbol' => 'racing.png'],
            ['simbol' => 'radiation.png'],
            ['simbol' => 'rain_2.png'],
            ['simbol' => 'rain_3.png'],
            ['simbol' => 'rattlesnake.png'],
            ['simbol' => 'realestate.png'],
            ['simbol' => 'realestate_2.png'],
            ['simbol' => 'recycle.png'],
            ['simbol' => 'recycle_2.png'],
            ['simbol' => 'recycle_3.png'],
            ['simbol' => 'regroup.png'],
            ['simbol' => 'regulier.png'],
            ['simbol' => 'resort.png'],
            ['simbol' => 'restaurant-barbecue.png'],
            ['simbol' => 'restaurant-buffet.png'],
            ['simbol' => 'restaurant-fish.png'],
            ['simbol' => 'restaurant-romantic.png'],
            ['simbol' => 'restaurant.png'],
            ['simbol' => 'restaurantafrican.png'],
            ['simbol' => 'restaurantchinese.png'],
            ['simbol' => 'restaurantchinese_2.png'],
            ['simbol' => 'restaurantfishchips.png'],
            ['simbol' => 'restaurantgourmet.png'],
            ['simbol' => 'restaurantgreek.png'],
            ['simbol' => 'restaurantindian.png'],
            ['simbol' => 'restaurantitalian.png'],
            ['simbol' => 'restaurantjapanese.png'],
            ['simbol' => 'restaurantjapanese_2.png'],
            ['simbol' => 'restaurantkebab.png'],
            ['simbol' => 'restaurantkorean.png'],
            ['simbol' => 'restaurantmediterranean.png'],
            ['simbol' => 'restaurantmexican.png'],
            ['simbol' => 'restaurantthai.png'],
            ['simbol' => 'restaurantturkish.png'],
            ['simbol' => 'restaurant_2.png'],
            ['simbol' => 'revolution.png'],
            ['simbol' => 'right.png'],
            ['simbol' => 'rightthendown.png'],
            ['simbol' => 'rightthenup.png'],
            ['simbol' => 'riparian.png'],
            ['simbol' => 'ropescourse.png'],
            ['simbol' => 'rowboat.png'],
            ['simbol' => 'rugby.png'],
            ['simbol' => 'ruins.png'],
            ['simbol' => 'sailboat-sport.png'],
            ['simbol' => 'sailboat-tourism.png'],
            ['simbol' => 'sailboat.png'],
            ['simbol' => 'salle-fete.png'],
            ['simbol' => 'satursday.png'],
            ['simbol' => 'satursday_2.png'],
            ['simbol' => 'sauna.png'],
            ['simbol' => 'school.png'],
            ['simbol' => 'school_2.png'],
            ['simbol' => 'schrink.png'],
            ['simbol' => 'schrink_2.png'],
            ['simbol' => 'sciencecenter.png'],
            ['simbol' => 'seals.png'],
            ['simbol' => 'seniorsite.png'],
            ['simbol' => 'shadow.png'],
            ['simbol' => 'shelter-picnic.png'],
            ['simbol' => 'shelter-sleeping.png'],
            ['simbol' => 'shoes.png'],
            ['simbol' => 'shoes_2.png'],
            ['simbol' => 'shoppingmall.png'],
            ['simbol' => 'shore.png'],
            ['simbol' => 'shower.png'],
            ['simbol' => 'sight.png'],
            ['simbol' => 'skateboarding.png'],
            ['simbol' => 'skiing.png'],
            ['simbol' => 'skiing_2.png'],
            ['simbol' => 'skijump.png'],
            ['simbol' => 'skilift.png'],
            ['simbol' => 'smallcity.png'],
            ['simbol' => 'smokingarea.png'],
            ['simbol' => 'sneakers.png'],
            ['simbol' => 'snow.png'],
            ['simbol' => 'snowboarding.png'],
            ['simbol' => 'snowmobiling.png'],
            ['simbol' => 'snowshoeing.png'],
            ['simbol' => 'soccer.png'],
            ['simbol' => 'soccer2.png'],
            ['simbol' => 'soccer_2.png'],
            ['simbol' => 'spaceport.png'],
            ['simbol' => 'spectacle.png'],
            ['simbol' => 'speed100.png'],
            ['simbol' => 'speed110.png'],
            ['simbol' => 'speed120.png'],
            ['simbol' => 'speed130.png'],
            ['simbol' => 'speed20.png'],
            ['simbol' => 'speed30.png'],
            ['simbol' => 'speed40.png'],
            ['simbol' => 'speed50.png'],
            ['simbol' => 'speed60.png'],
            ['simbol' => 'speed70.png'],
            ['simbol' => 'speed80.png'],
            ['simbol' => 'speed90.png'],
            ['simbol' => 'speedhump.png'],
            ['simbol' => 'spelunking.png'],
            ['simbol' => 'stadium.png'],
            ['simbol' => 'statue.png'],
            ['simbol' => 'steamtrain.png'],
            ['simbol' => 'stop.png'],
            ['simbol' => 'stoplight.png'],
            ['simbol' => 'stoplight_2.png'],
            ['simbol' => 'strike.png'],
            ['simbol' => 'strike1.png'],
            ['simbol' => 'subway.png'],
            ['simbol' => 'sun.png'],
            ['simbol' => 'sunday.png'],
            ['simbol' => 'sunday_2.png'],
            ['simbol' => 'sun_2.png'],
            ['simbol' => 'supermarket.png'],
            ['simbol' => 'supermarket_2.png'],
            ['simbol' => 'surfing.png'],
            ['simbol' => 'suv.png'],
            ['simbol' => 'synagogue.png'],
            ['simbol' => 'tailor.png'],
            ['simbol' => 'tapas.png'],
            ['simbol' => 'taxi.png'],
            ['simbol' => 'taxiway.png'],
            ['simbol' => 'taxi_2.png'],
            ['simbol' => 'teahouse.png'],
            ['simbol' => 'telephone.png'],
            ['simbol' => 'templehindu.png'],
            ['simbol' => 'tennis.png'],
            ['simbol' => 'tennis2.png'],
            ['simbol' => 'tennis_2.png'],
            ['simbol' => 'tent.png'],
            ['simbol' => 'terrace.png'],
            ['simbol' => 'text.png'],
            ['simbol' => 'textiles.png'],
            ['simbol' => 'theater.png'],
            ['simbol' => 'theater_2.png'],
            ['simbol' => 'themepark.png'],
            ['simbol' => 'thunder.png'],
            ['simbol' => 'thunder_2.png'],
            ['simbol' => 'thursday.png'],
            ['simbol' => 'thursday_2.png'],
            ['simbol' => 'toilets.png'],
            ['simbol' => 'toilets_2.png'],
            ['simbol' => 'tollstation.png'],
            ['simbol' => 'tools.png'],
            ['simbol' => 'tower.png'],
            ['simbol' => 'toys.png'],
            ['simbol' => 'toys_2.png'],
            ['simbol' => 'trafficenforcementcamera.png'],
            ['simbol' => 'train.png'],
            ['simbol' => 'train_2.png'],
            ['simbol' => 'tram.png'],
            ['simbol' => 'trash.png'],
            ['simbol' => 'truck.png'],
            ['simbol' => 'truck_2.png'],
            ['simbol' => 'tuesday.png'],
            ['simbol' => 'tuesday_2.png'],
            ['simbol' => 'tunnel.png'],
            ['simbol' => 'turnleft.png'],
            ['simbol' => 'turnright.png'],
            ['simbol' => 'university.png'],
            ['simbol' => 'university_2.png'],
            ['simbol' => 'unnamed.png'],
            ['simbol' => 'up.png'],
            ['simbol' => 'upleft.png'],
            ['simbol' => 'upright.png'],
            ['simbol' => 'upthenleft.png'],
            ['simbol' => 'upthenright.png'],
            ['simbol' => 'usfootball.png'],
            ['simbol' => 'vespa.png'],
            ['simbol' => 'vet.png'],
            ['simbol' => 'video.png'],
            ['simbol' => 'videogames.png'],
            ['simbol' => 'videogames_2.png'],
            ['simbol' => 'villa.png'],
            ['simbol' => 'waitingroom.png'],
            ['simbol' => 'water.png'],
            ['simbol' => 'waterfall.png'],
            ['simbol' => 'watermill.png'],
            ['simbol' => 'waterpark.png'],
            ['simbol' => 'waterskiing.png'],
            ['simbol' => 'watertower.png'],
            ['simbol' => 'waterwell.png'],
            ['simbol' => 'waterwellpump.png'],
            ['simbol' => 'wedding.png'],
            ['simbol' => 'wednesday.png'],
            ['simbol' => 'wednesday_2.png'],
            ['simbol' => 'wetland.png'],
            ['simbol' => 'white1.png'],
            ['simbol' => 'white20.png'],
            ['simbol' => 'wifi.png'],
            ['simbol' => 'wifi_2.png'],
            ['simbol' => 'windmill.png'],
            ['simbol' => 'windsurfing.png'],
            ['simbol' => 'windturbine.png'],
            ['simbol' => 'winery.png'],
            ['simbol' => 'wineyard.png'],
            ['simbol' => 'workoffice.png'],
            ['simbol' => 'world.png'],
            ['simbol' => 'worldheritagesite.png'],
            ['simbol' => 'yoga.png'],
            ['simbol' => 'youthhostel.png'],
            ['simbol' => 'zipline.png'],
            ['simbol' => 'zoo.png'],
            ['simbol' => 'zoo_2.png'],
        ];

        return $simbols;
    }
}
