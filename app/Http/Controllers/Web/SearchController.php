<?php

namespace App\Http\Controllers\Web;

use App\Enums\KategoriSasaranBantuanEnum;
use App\Enums\SasaranStatistikKeluargaEnum;
use App\Http\Controllers\Controller;
use App\Models\Enums\StatistikPendudukEnum;
use App\Models\Enums\StatistikRtmEnum;
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
        $totalDesa = 0;
        $pendudukSummary = 0;
        $configSummary = 0;
        $bantuanSummary = 0;
        $categoriesItems = [
            ['text' => 'penduduk', 'key' => 'penduduk', 'value' => $pendudukSummary, 'icon' => 'web/img/penduduk.jpg'],
            ['text' => 'keluarga', 'key' => 'keluarga', 'value' => 0, 'icon' => 'web/img/keluarga.jpg'],
            ['text' => 'RTM', 'key' => 'rtm', 'value' => 0, 'icon' => 'web/img/kelurahan.jpg'],
            ['text' => 'bantuan', 'key' => 'bantuan', 'value' => $bantuanSummary, 'icon' => 'web/img/bantuan.jpg'],
        ];
        $groupStatistik = [
            ['text' => 'Penduduk', 'key' => 'penduduk', 'items' => StatistikPendudukEnum::allKeyLabel(), 'icon' => 'fa-pie-chart'],
            ['text' => 'Keluarga', 'key' => 'keluarga', 'items' => SasaranStatistikKeluargaEnum::KATEGORI_STATISTIK, 'icon' => 'fa-bar-chart'],
            ['text' => 'Bantuan', 'key' => 'bantuan', 'items' => KategoriSasaranBantuanEnum::KATEGORI_STATISTIK, 'icon' => 'fa-line-chart'],
            ['text' => 'RTM', 'key' => 'rtm', 'items' => StatistikRtmEnum::allKeyLabel(), 'icon' => 'fa-area-chart'],
        ];
        $view = view('web.partials.statistik_result', compact('categoriesItems', 'desa', 'groupStatistik'))->render();

        return response()->json(['content' => $view]);
    }
}
