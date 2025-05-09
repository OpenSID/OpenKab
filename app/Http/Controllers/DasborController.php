<?php

namespace App\Http\Controllers;

use App\Models\Identitas;
use App\Models\Penduduk;

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
            ['key' => 'kecamatan', 'text' => 'kecamatan', 'value' => $configSummary, 'icon' => 'web/img/kecamatan.jpg'],
            ['key' => 'desa', 'text' => 'desa/kelurahan', 'value' => $totalDesa, 'icon' => 'web/img/kelurahan.jpg'],
            ['key' => 'penduduk', 'text' => 'jumlah penduduk', 'value' => $pendudukSummary, 'icon' => 'web/img/penduduk.jpg'],
            ['key' => 'keluarga', 'text' => 'jumlah keluarga', 'value' => $keluargaSummary, 'icon' => 'web/img/bantuan.jpg'],
        ];

        return view('dasbor.index', compact('data', 'categoriesItems'));
    }
}
