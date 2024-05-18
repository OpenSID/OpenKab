<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

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
            ['key' => 'desa', 'text' => 'desa/kelurahan', 'value' => $totalDesa, 'icon' => 'web/img/kelurahan.jpg'],
            ['key' => 'penduduk', 'text' => 'jumlah penduduk', 'value' => $pendudukSummary, 'icon' => 'web/img/penduduk.jpg'],
            ['key' => 'keluarga', 'text' => 'jumlah keluarga', 'value' => $keluargaSummary, 'icon' => 'web/img/bantuan.jpg'],
        ];
        $listKecamatan = ['' => 'Pilih Kecamatan'];
        $listDesa = ['' => 'Pilih Desa'];

        return view('presisi.index', compact('categoriesItems', 'listKecamatan', 'listDesa'));
    }
}
