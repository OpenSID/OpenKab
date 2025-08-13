<?php

namespace App\Http\Controllers\Web;

use App\Enums\AccessTypeEnum;
use App\Enums\KeluargaKategoriStatistikEnum;
use App\Http\Controllers\Controller;
use App\Models\Enums\StatistikPendudukEnum;
use App\Models\Enums\StatistikRtmEnum;
use App\Services\KesehatanApiService;
use App\Services\PemetaanService;

class PresisiController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $totalDesa = 0;
        $pendudukSummary = 0;
        $configSummary = 0;
        $keluargaSummary = 0;
        $categoriesItems = [
            ['key' => 'kecamatan', 'text' => 'kecamatan', 'value' => $configSummary, 'icon' => 'web/img/kecamatan.jpg'],
            ['key' => 'desa', 'text' => config('app.sebutanDesa'), 'value' => $totalDesa, 'icon' => 'web/img/kelurahan.jpg'],
            ['key' => 'penduduk', 'text' => 'jumlah penduduk', 'value' => $pendudukSummary, 'icon' => 'web/img/penduduk.jpg'],
            ['key' => 'keluarga', 'text' => 'jumlah keluarga', 'value' => $keluargaSummary, 'icon' => 'web/img/bantuan.jpg'],
        ];
        $listKecamatan = ['' => 'Pilih Kecamatan'];
        $listDesa = ['' => 'Pilih '.config('app.sebutanDesa')];

        return view('presisi.index', compact('categoriesItems', 'listKecamatan', 'listDesa'));
    }

    public function kependudukan($id = '')
    {
        $totalDesa = 0;
        $pendudukSummary = 0;
        $configSummary = 0;
        $keluargaSummary = 0;
        $categoriesItems = [
            ['key' => 'kecamatan', 'text' => 'kecamatan', 'value' => $configSummary, 'icon' => 'web/img/kecamatan.jpg'],
            ['key' => 'desa', 'text' => config('app.sebutanDesa'), 'value' => $totalDesa, 'icon' => 'web/img/kelurahan.jpg'],
            ['key' => 'penduduk', 'text' => 'jumlah penduduk', 'value' => $pendudukSummary, 'icon' => 'web/img/penduduk.jpg'],
            ['key' => 'keluarga', 'text' => 'jumlah keluarga', 'value' => $keluargaSummary, 'icon' => 'web/img/bantuan.jpg'],
        ];
        $statistik = StatistikPendudukEnum::KATEGORI_STATISTIK;

        return view('presisi.kependudukan.index', compact('statistik', 'id', 'categoriesItems'));
    }

    public function kesehatan($kuartal = null, $tahun = null, $id = null, $kabupaten = null, $kecamatan = null, $desa = null)
    {
        // Cek apakah semua parameter null
        if (($kuartal == 'null') && ($tahun == 'null') && ($id == 'null') && ($kabupaten == 'null') && ($kecamatan == 'null') && ($desa == 'null')) {
            // Redirect ke link tertentu jika semua parameter null
            return redirect()->route('presisi.kesehatan'); // Ganti dengan nama route yang sesuai
        }

        $totalDesa = 0;
        $pendudukSummary = 0;
        $configSummary = 0;
        $keluargaSummary = 0;
        $categoriesItems = [
            ['key' => 'kecamatan', 'text' => 'kecamatan', 'value' => $configSummary, 'icon' => 'web/img/kecamatan.jpg'],
            ['key' => 'desa', 'text' => config('app.sebutanDesa'), 'value' => $totalDesa, 'icon' => 'web/img/kelurahan.jpg'],
            ['key' => 'penduduk', 'text' => 'jumlah penduduk', 'value' => $pendudukSummary, 'icon' => 'web/img/penduduk.jpg'],
            ['key' => 'keluarga', 'text' => 'jumlah keluarga', 'value' => $keluargaSummary, 'icon' => 'web/img/bantuan.jpg'],
        ];

        if ($kuartal < 1 || $kuartal > 4) {
            $kuartal = null;
        }

        if ($kuartal == null) {
            $bulanSekarang = date('m');
            if ($bulanSekarang <= 3) {
                $kuartal = 1;
            } elseif ($bulanSekarang <= 6) {
                $kuartal = 2;
            } elseif ($bulanSekarang <= 9) {
                $kuartal = 3;
            } elseif ($bulanSekarang <= 12) {
                $kuartal = 4;
            }
        }
        $kuartalget = $kuartal;
        if ($tahun == null) {
            $tahun = date('Y');
        }

        $data = $this->sumberData($kuartal, $tahun, $id, $kabupaten, $kecamatan, $desa);

        return view('presisi.kesehatan.index', compact('data', 'categoriesItems', 'kuartalget', 'tahun', 'id', 'kabupaten', 'kecamatan', 'desa'));
    }

    private function sumberData($kuartal = null, $tahun = null, $id = null, $kabupaten = null, $kecamatan = null, $desa = null)
    {
        $service = new KesehatanApiService;
        $filters = [
            'kuartal' => $kuartal == 'null' ? null : $kuartal,
            'tahun' => $tahun == 'null' ? null : $tahun,
            'posyandu' => $id == 'null' ? null : $id,
            'kabupaten' => $kabupaten == 'null' ? null : $kabupaten,
            'kode_kecamatan' => $kecamatan == 'null' ? null : $kecamatan,
            'desa' => $desa == 'null' ? null : $desa,
        ];

        $data = $service->data($filters)->first();

        return $data;
    }

    public function rtm($id = '')
    {
        $statistik = StatistikRtmEnum::allKeyLabel();
        $totalDesa = 0;
        $pendudukSummary = 0;
        $configSummary = 0;
        $keluargaSummary = 0;
        $categoriesItems = [
            ['key' => 'kecamatan', 'text' => 'kecamatan', 'value' => $configSummary, 'icon' => 'web/img/kecamatan.jpg'],
            ['key' => 'desa', 'text' => config('app.sebutanDesa'), 'value' => $totalDesa, 'icon' => 'web/img/kelurahan.jpg'],
            ['key' => 'penduduk', 'text' => 'jumlah penduduk', 'value' => $pendudukSummary, 'icon' => 'web/img/penduduk.jpg'],
            ['key' => 'keluarga', 'text' => 'jumlah keluarga', 'value' => $keluargaSummary, 'icon' => 'web/img/bantuan.jpg'],
        ];

        return view('presisi.rtm.index', compact('statistik', 'id', 'categoriesItems'));
    }

    public function keluarga($id = '')
    {
        if ($id != 'kelas-sosial') {
            $id = '';
        }
        $statistik = KeluargaKategoriStatistikEnum::KATEGORI_STATISTIK;
        $totalDesa = 0;
        $pendudukSummary = 0;
        $configSummary = 0;
        $keluargaSummary = 0;
        $categoriesItems = [
            ['key' => 'kecamatan', 'text' => 'kecamatan', 'value' => $configSummary, 'icon' => 'web/img/kecamatan.jpg'],
            ['key' => 'desa', 'text' => config('app.sebutanDesa'), 'value' => $totalDesa, 'icon' => 'web/img/kelurahan.jpg'],
            ['key' => 'penduduk', 'text' => 'jumlah penduduk', 'value' => $pendudukSummary, 'icon' => 'web/img/penduduk.jpg'],
            ['key' => 'keluarga', 'text' => 'jumlah keluarga', 'value' => $keluargaSummary, 'icon' => 'web/img/bantuan.jpg'],
        ];

        return view('presisi.keluarga.index', compact('statistik', 'id', 'categoriesItems'));
    }

    public function bantuan($id = '')
    {
        $totalDesa = 0;
        $pendudukSummary = 0;
        $configSummary = 0;
        $keluargaSummary = 0;
        $categoriesItems = [
            ['key' => 'kecamatan', 'text' => 'kecamatan', 'value' => $configSummary, 'icon' => 'web/img/kecamatan.jpg'],
            ['key' => 'desa', 'text' => config('app.sebutanDesa'), 'value' => $totalDesa, 'icon' => 'web/img/kelurahan.jpg'],
            ['key' => 'penduduk', 'text' => 'jumlah penduduk', 'value' => $pendudukSummary, 'icon' => 'web/img/penduduk.jpg'],
            ['key' => 'keluarga', 'text' => 'jumlah keluarga', 'value' => $keluargaSummary, 'icon' => 'web/img/bantuan.jpg'],
        ];

        return view('presisi.bantuan.index', compact('id', 'categoriesItems'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function geoSpasial()
    {
        $totalDesa = 0;
        $pendudukSummary = 0;
        $configSummary = 0;
        $keluargaSummary = 0;
        $categoriesItems = [
            ['key' => 'kecamatan', 'text' => 'kecamatan', 'value' => $configSummary, 'icon' => 'web/img/kecamatan.jpg'],
            ['key' => 'desa', 'text' => config('app.sebutanDesa'), 'value' => $totalDesa, 'icon' => 'web/img/kelurahan.jpg'],
            ['key' => 'penduduk', 'text' => 'jumlah penduduk', 'value' => $pendudukSummary, 'icon' => 'web/img/penduduk.jpg'],
            ['key' => 'keluarga', 'text' => 'jumlah keluarga', 'value' => $keluargaSummary, 'icon' => 'web/img/bantuan.jpg'],
        ];
        $listKecamatan = ['' => 'Pilih Kecamatan'];
        $listDesa = ['' => 'Pilih Desa'];

        $data = (new PemetaanService)->getAllPoint([
            'filter[tipe]' => AccessTypeEnum::ROOT->value(),
        ]);

        $object = json_decode(json_encode($data));

        $kategori = (array) $object;

        return view('presisi.geo_spasial.index', compact('categoriesItems', 'listKecamatan', 'listDesa', 'kategori'));
    }
}
