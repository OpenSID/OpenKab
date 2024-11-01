<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\BantuanRepository;
use App\Http\Repository\ConfigRepository;
use App\Http\Repository\KeluargaRepository;
use App\Http\Repository\PendudukRepository;
use App\Models\Config;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class WebsiteController extends Controller
{
    public function __invoke()
    {
        return response()->json([
            'data' => $this->data(),
            'message' => 'Berhasil mengambil data utama website',
        ], Response::HTTP_OK);
    }

    public function getKecamatan($kode_kabupaten)
    {
        return QueryBuilder::for(Config::class)
            ->where('kode_kabupaten', $kode_kabupaten)
            ->select(DB::raw('MAX(nama_kecamatan) as nama_kecamatan'), DB::raw('MIN(id) as id'))
            ->groupBy('kode_kecamatan')
            ->pluck('nama_kecamatan', 'id');
    }

    private function data()
    {
        $totalDesa = 0;
        $configSummary = (new ConfigRepository)->desa()->groupBy('nama_kecamatan')->map(function ($item) use (&$totalDesa) {
            $totalDesa += $item->count();

            return $item->pluck('nama_desa', 'id');
        });

        $configSummaryKecamatan = (new ConfigRepository)->kabupaten()->mapWithKeys(function ($item) {
            return [
                $item->nama_kabupaten => $this->getKecamatan($item->kode_kabupaten),
            ];
        });

        $result_kabupaten = (new ConfigRepository)->kabupaten();
        $result_kecamatan = (new ConfigRepository)->kecamatan();

        $bantuanSummary = (new BantuanRepository)->summary();
        $pendudukSummary = (new PendudukRepository)->summary();
        $keluargaSummary = (new KeluargaRepository)->summary();
        $categoriesItems = [
            'keluarga' => ['text' => 'keluarga', 'value' => angka_lokal($keluargaSummary), 'icon' => 'web/img/penduduk.jpg'],
            'penduduk' => ['text' => 'penduduk', 'value' => angka_lokal($pendudukSummary), 'icon' => 'web/img/penduduk.jpg'],
            'kabupaten' => ['text' => 'kabupaten', 'value' => angka_lokal($result_kabupaten->count()) ?? 0, 'icon' => 'web/img/kecamatan.jpg'],
            'kecamatan' => ['text' => 'kecamatan', 'value' => angka_lokal($result_kecamatan->count()) ?? 0, 'icon' => 'web/img/kecamatan.jpg'],
            'desa' => ['text' => 'desa/kelurahan', 'value' => angka_lokal($totalDesa), 'icon' => 'web/img/kelurahan.jpg'],
            'bantuan' => ['text' => 'bantuan', 'value' => angka_lokal($bantuanSummary), 'icon' => 'web/img/bantuan.jpg'],
        ];
        $listKabupaten = $result_kabupaten->pluck('nama_kabupaten', 'nama_kabupaten');
        $listKecamatan = $configSummaryKecamatan->toArray();
        $listDesa = $configSummary->toArray();

        return compact('categoriesItems', 'listKabupaten', 'listKecamatan', 'listDesa');
    }
}
