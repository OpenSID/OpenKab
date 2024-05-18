<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\BantuanRepository;
use App\Http\Repository\ConfigRepository;
use App\Http\Repository\KeluargaRepository;
use App\Http\Repository\PendudukRepository;
use Illuminate\Http\Response;

class WebsiteController extends Controller
{
    public function __invoke()
    {
        return response()->json([
            'data' => $this->data(),
            'message' => 'Berhasil mengambil data utama website',
        ], Response::HTTP_OK);
    }

    private function data()
    {
        $totalDesa = 0;
        $configSummary = (new ConfigRepository)->desa()->groupBy('nama_kecamatan')->map(function ($item) use (&$totalDesa) {
            $totalDesa += $item->count();

            return $item->pluck('nama_desa', 'id');
        });

        $bantuanSummary = (new BantuanRepository)->summary();
        $pendudukSummary = (new PendudukRepository)->summary();
        $keluargaSummary = (new KeluargaRepository)->summary();
        $categoriesItems = [
            'keluarga' => ['text' => 'keluarga', 'value' => angka_lokal($keluargaSummary), 'icon' => 'web/img/penduduk.jpg'],
            'penduduk' => ['text' => 'penduduk', 'value' => angka_lokal($pendudukSummary), 'icon' => 'web/img/penduduk.jpg'],
            'kecamatan' => ['text' => 'kecamatan', 'value' => angka_lokal($configSummary->count()) ?? 0, 'icon' => 'web/img/kecamatan.jpg'],
            'desa' => ['text' => 'desa/kelurahan', 'value' => angka_lokal($totalDesa), 'icon' => 'web/img/kelurahan.jpg'],
            'bantuan' => ['text' => 'bantuan', 'value' => angka_lokal($bantuanSummary), 'icon' => 'web/img/bantuan.jpg'],
        ];
        $listKecamatan = array_combine($configSummary->keys()->toArray(), $configSummary->keys()->toArray());
        $listDesa = $configSummary->toArray();

        return compact('categoriesItems', 'listKecamatan', 'listDesa');
    }
}
