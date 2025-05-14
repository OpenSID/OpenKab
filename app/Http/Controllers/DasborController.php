<?php

namespace App\Http\Controllers;

use App\Models\Identitas;

class DasborController extends Controller
{
    public function index()
    {
        $identitas = new Identitas();
        $data = $identitas->pengaturan();
        $totalDesa = 0;
        $pendudukSummary = 0;
        $configSummary = 0;
        $keluargaSummary = 0;
        $categoriesItems = [
            ['key' => 'kecamatan', 'url' => url('kecamatan'), 'text' => 'kecamatan', 'value' => $configSummary, 'icon' => 'web/img/kecamatan.jpg'],
            ['key' => 'desa', 'url' => url('desa'), 'text' => 'desa/kelurahan', 'value' => $totalDesa, 'icon' => 'web/img/kelurahan.jpg'],
            ['key' => 'penduduk', 'url' => url('penduduk'), 'text' => 'jumlah penduduk', 'value' => $pendudukSummary, 'icon' => 'web/img/penduduk.jpg'],
            ['key' => 'keluarga', 'url' => url('keluarga'), 'text' => 'jumlah keluarga', 'value' => $keluargaSummary, 'icon' => 'web/img/bantuan.jpg'],
        ];

        return view('dasbor.index', compact('data', 'categoriesItems'));
    }
}
