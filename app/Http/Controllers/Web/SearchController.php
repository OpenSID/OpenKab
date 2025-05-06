<?php

namespace App\Http\Controllers\Web;

use App\Enums\KategoriSasaranBantuanEnum;
use App\Enums\PendudukKategoriStatistikEnum;
use App\Enums\RtmKategoriStatistikEnum;
use App\Enums\SasaranStatistikKeluargaEnum;
use App\Http\Controllers\Controller;
use App\Models\Bantuan;
use App\Models\Config;
use App\Models\Keluarga;
use App\Models\Penduduk;
use App\Models\Rtm;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $desa = Config::find($request->get('config_desa'));
        $categoriesItems = [
            ['text' => 'penduduk', 'key' => 'penduduk', 'value' => angka_lokal(Penduduk::filterDesa()->count()), 'icon' => 'web/img/penduduk.jpg'],
            ['text' => 'keluarga', 'key' => 'keluarga', 'value' => angka_lokal(Keluarga::filterDesa()->count()), 'icon' => 'web/img/keluarga.jpg'],
            ['text' => 'RTM', 'key' => 'rtm', 'value' => angka_lokal(Rtm::filterDesa()->count()), 'icon' => 'web/img/kelurahan.jpg'],
            ['text' => 'bantuan', 'key' => 'bantuan', 'value' => angka_lokal(Bantuan::filterDesa()->count()), 'icon' => 'web/img/bantuan.jpg'],
        ];
        $groupStatistik = [
            ['text' => 'Penduduk', 'key' => 'penduduk', 'items' => PendudukKategoriStatistikEnum::KATEGORI_STATISTIK, 'icon' => 'fa-pie-chart'],
            ['text' => 'Keluarga', 'key' => 'keluarga', 'items' => SasaranStatistikKeluargaEnum::KATEGORI_STATISTIK, 'icon' => 'fa-bar-chart'],
            ['text' => 'Bantuan', 'key' => 'bantuan', 'items' => KategoriSasaranBantuanEnum::KATEGORI_STATISTIK, 'icon' => 'fa-line-chart'],
            ['text' => 'RTM', 'key' => 'rtm', 'items' => RtmKategoriStatistikEnum::KATEGORI_STATISTIK, 'icon' => 'fa-area-chart'],
        ];
        $view = view('web.partials.statistik_result', compact('categoriesItems', 'desa', 'groupStatistik'))->render();

        return response()->json(['content' => $view]);
    }
}
