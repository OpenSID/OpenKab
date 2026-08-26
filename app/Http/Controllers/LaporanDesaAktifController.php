<?php

namespace App\Http\Controllers;

use App\Services\ApiProxyService;
use Illuminate\Http\Request;

class LaporanDesaAktifController extends Controller
{
    protected ApiProxyService $apiProxyService;

    public function __construct(ApiProxyService $apiProxyService)
    {
        $this->apiProxyService = $apiProxyService;
    }

    public function index(Request $request)
    {
        $title = 'Laporan Desa Aktif';

        return view('laporan.desa_aktif.index', compact('title'));
    }

    protected function getDesaAktifData(Request $request): array
    {
        $filter = array_filter($request->all());

        // Build parameters for API call
        $params = [];

        // Add kode_kabupaten parameter
        $params['filter[kode_kabupaten]'] = session('kabupaten.kode_kabupaten') ?? config('app.kodeKabupatenApi');

        // Add kode_kecamatan parameter if exists
        if (session('kecamatan.kode_kecamatan')) {
            $params['filter[kode_kecamatan]'] = session('kecamatan.kode_kecamatan');
        }

        // Add search parameter if exists
        if (! empty($filter['search'])) {
            $params['filter[search]'] = $filter['search'];
        }

        // Make API call to get desa-aktif data (without pagination for print)
        $params['page[size]'] = 10000; // Large number to get all data
        $params['page[number]'] = 1;

        // Use ApiProxyService to get data
        $response = $this->apiProxyService->get('desa-aktif', $params);
        $data = $response['data'] ?? [];

        return array_map(function ($item) {
            $item['attributes']['status_aktif'] = $this->hitungStatusAktif($item['attributes'] ?? []);

            return $item;
        }, $data);
    }

    protected function hitungStatusAktif(array $attributes): string
    {
        $loginTerakhir = ! empty($attributes['login_terakhir']) && $attributes['login_terakhir'] !== '0000-00-00' && $attributes['login_terakhir'] !== '0000-00-00 00:00:00'
            ? $attributes['login_terakhir'] : null;
        $perubahanTerakhir = ! empty($attributes['perubahan_terakhir']) && $attributes['perubahan_terakhir'] !== '0000-00-00' && $attributes['perubahan_terakhir'] !== '0000-00-00 00:00:00'
            ? $attributes['perubahan_terakhir'] : null;

        $batas = \Carbon\Carbon::now()->subDays(7);

        $loginAktif = false;
        if ($loginTerakhir) {
            try {
                $loginAktif = \Carbon\Carbon::parse($loginTerakhir)->gte($batas);
            } catch (\Exception $e) {
                $loginAktif = false;
            }
        }

        $perubahanAktif = false;
        if ($perubahanTerakhir) {
            try {
                $perubahanAktif = \Carbon\Carbon::parse($perubahanTerakhir)->gte($batas);
            } catch (\Exception $e) {
                $perubahanAktif = false;
            }
        }

        return ($loginAktif || $perubahanAktif) ? 'Aktif' : 'Tidak Aktif';
    }

    public function cetak(Request $request)
    {
        $data = $this->getDesaAktifData($request);

        return view('laporan.desa_aktif.cetak', compact('data'));
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getDesaAktifData($request);
        $excel = true;

        $html = view('laporan.desa_aktif.cetak', compact('data', 'excel'))->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="laporan-desa-aktif.xls"');
    }
}
