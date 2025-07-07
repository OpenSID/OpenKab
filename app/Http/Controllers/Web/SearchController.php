<?php

namespace App\Http\Controllers\Web;

use App\Enums\KategoriSasaranBantuanEnum;
use App\Enums\SasaranStatistikKeluargaEnum;
use App\Http\Controllers\Controller;
use App\Models\Enums\StatistikPendudukEnum;
use App\Models\Enums\StatistikRtmEnum;
use App\Services\ConfigApiService;
use App\Services\SummaryWebsiteService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SearchController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $desa = (new ConfigApiService)->desaByKode($request->get('config_desa'));
        $summaryDesa = (new SummaryWebsiteService)->getSummaryData(['filter[kode_desa]' => $request->get('config_desa')]);
        $categories = $summaryDesa['categoriesItems'] ?? [
            'penduduk' => ['value' => 0],
            'keluarga' => ['value' => 0],
            'rtm' => ['value' => 0],
            'bantuan' => ['value' => 0],
        ];
        $categoriesItems = [
            ['text' => 'penduduk', 'key' => 'penduduk', 'value' => $categories['penduduk']['value'] ?? 0, 'icon' => 'web/img/penduduk.jpg'],
            ['text' => 'keluarga', 'key' => 'keluarga', 'value' => $categories['keluarga']['value'] ?? 0, 'icon' => 'web/img/keluarga.jpg'],
            ['text' => 'RTM', 'key' => 'rtm', 'value' => $categories['rtm']['value'] ?? 0, 'icon' => 'web/img/kelurahan.jpg'],
            ['text' => 'bantuan', 'key' => 'bantuan', 'value' => $categories['bantuan']['value'] ?? 0, 'icon' => 'web/img/bantuan.jpg'],
        ];
        $groupStatistik = [
            ['text' => 'Penduduk', 'key' => 'penduduk', 'items' => StatistikPendudukEnum::KATEGORI_STATISTIK, 'icon' => 'fa-pie-chart'],
            ['text' => 'Keluarga', 'key' => 'keluarga', 'items' => SasaranStatistikKeluargaEnum::KATEGORI_STATISTIK, 'icon' => 'fa-bar-chart'],
            ['text' => 'Bantuan', 'key' => 'bantuan', 'items' => KategoriSasaranBantuanEnum::KATEGORI_STATISTIK, 'icon' => 'fa-line-chart'],
            ['text' => 'RTM', 'key' => 'rtm', 'items' => StatistikRtmEnum::allKeyLabel(), 'icon' => 'fa-area-chart'],
        ];
        $view = view('web.partials.statistik_result', compact('categoriesItems', 'desa', 'groupStatistik'))->render();

        return response()->json(['content' => $view]);
    }
}
