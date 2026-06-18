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

    public function cetak(Request $request)
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
        return view('laporan.desa_aktif.cetak', compact('data'));
    }
}
