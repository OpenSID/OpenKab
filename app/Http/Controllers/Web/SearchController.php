<?php

namespace App\Http\Controllers\Web;

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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        $desa = Config::find($request->get('config_desa'));
        $categoriesItems = [
            ['text' => 'penduduk','value' => Penduduk::filterDesa()->count(), 'icon' => 'web/img/penduduk.jpg'],
            ['text' => 'keluarga','value' => Keluarga::filterDesa()->count(), 'icon' => 'web/img/keluarga.jpg'],
            ['text' => 'RTM','value' => Rtm::filterDesa()->count(), 'icon' => 'web/img/kelurahan.jpg'],
            ['text' => 'bantuan','value' => Bantuan::filterDesa()->count(), 'icon' => 'web/img/bantuan.jpg'],
        ];
        $groupStatistik = [
            ['text' => 'Penduduk', 'key' => 'penduduk', 'items' => Penduduk::KATEGORI_STATISTIK, 'icon' => 'fa-pie-chart'],
            ['text' => 'Keluarga', 'key' => 'keluarga', 'items' => Keluarga::KATEGORI_STATISTIK, 'icon' => 'fa-bar-chart'],
            ['text' => 'Bantuan', 'key' => 'bantuan', 'items' => Bantuan::KATEGORI_STATISTIK, 'icon' => 'fa-line-chart'],
            ['text' => 'RTM', 'key' => 'rtm', 'items' => Rtm::KATEGORI_STATISTIK, 'icon' => 'fa-area-chart'],
        ];
        $view = view('web.partials.statistik_result', compact('categoriesItems', 'desa', 'groupStatistik'))->render();
        return response()->json(['content' => $view]);
    }
}
