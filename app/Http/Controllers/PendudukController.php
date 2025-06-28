<?php

namespace App\Http\Controllers;

use App\Models\Enums\JenisKelaminEnum;
use App\Models\Enums\StatusEnum;
use App\Services\PendudukApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PendudukController extends Controller
{
    protected $permission = 'penduduk';

    public function index()
    {
        $listPermission = $this->generateListPermission();
        $filters = request('filter', [
            'kode_kabupaten' => null,
            'kode_desa' => null,
            'kode_kecamatan' => null,
            'sex' => '',
        ]);
        if (isset($filters['kriteria'])) {
            parse_str($filters['kriteria'], $kriteria);
            unset($filters['kriteria']);
            if ($kriteria) {
                foreach ($kriteria as $key => $value) {
                    $filters[$key] = $value;
                    if ($key === 'hamil') {
                        $filters['sex'] = JenisKelaminEnum::perempuan;
                    }
                }
                if (isset($kriteria['belum_mengisi']) && $kriteria['belum_mengisi'] === 'status-kehamilan') {
                    $filters['sex'] = JenisKelaminEnum::perempuan;
                }
                if (isset($kriteria['jumlah']) && $kriteria['jumlah'] === 'status-kehamilan') {
                    $filters['sex'] = JenisKelaminEnum::perempuan;
                }
                if (isset($kriteria['total']) && $kriteria['total'] === 'status-kehamilan') {
                    $filters['sex'] = JenisKelaminEnum::perempuan;
                }
                $filters['status'] = StatusEnum::aktif;
            }
        }

        $judul = request('judul', '');

        if (request()->has('chart-view')) {
            $chart = $this->chart();

            return view('penduduk.index', compact('filters', 'judul', 'chart'))->with($listPermission);
        }

        $chart = [];

        return view('penduduk.index', compact('filters', 'judul', 'chart'))->with($listPermission);
    }

    public function chart()
    {
        return [
            'kategori' => Str::slug(strtolower(request()->tipe)),
            'nama' => request()->nama,
            'view' => true,
        ];
    }

    public function show($id_penduduk)
    {
        $result = (new PendudukApiService())->penduduk([
            'filter[id]' => $id_penduduk,
        ]);

        $penduduk = $result->first();

        return view('penduduk.detail', compact('penduduk', 'id_penduduk'));
    }

    public function pindah($id)
    {
        return view('penduduk.pindah');
    }

    public function cetak(Request $request)
    {
        return view('penduduk.cetak', ['filter' => $request->getQueryString()]);
    }
}
