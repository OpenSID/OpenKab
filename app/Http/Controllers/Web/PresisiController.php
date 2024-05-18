<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;

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
        $bantuanSummary = 0;
        $categoriesItems = [
            ['key' => 'penduduk', 'text' => 'penduduk', 'value' => $pendudukSummary, 'icon' => 'web/img/penduduk.jpg'],
            ['key' => 'kecamatan', 'text' => 'kecamatan', 'value' => $configSummary, 'icon' => 'web/img/kecamatan.jpg'],
            ['key' => 'desa', 'text' => 'desa/kelurahan', 'value' => $totalDesa, 'icon' => 'web/img/kelurahan.jpg'],
            ['key' => 'bantuan', 'text' => 'bantuan', 'value' => $bantuanSummary, 'icon' => 'web/img/bantuan.jpg'],
        ];
        $listKecamatan = ['' => 'Pilih Kecamatan'];
        $listDesa = ['' => 'Pilih Desa'];

        return view('presisi.index', compact('categoriesItems', 'listKecamatan', 'listDesa'));
    }
}
