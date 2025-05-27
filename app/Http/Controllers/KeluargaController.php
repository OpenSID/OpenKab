<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KeluargaController extends Controller
{
    public function index()
    {
        $filters = request('filter', [
            'kode_kabupaten' => null,
            'kode_desa' => null,
            'kode_kecamatan' => null,
        ]);

        if (isset($filters['kriteria'])) {
            parse_str($filters['kriteria'], $kriteria);
            unset($filters['kriteria']);
            if ($kriteria) {
                foreach ($kriteria as $key => $value) {
                    $filters[$key] = $value;
                }
            }
        }

        $judul = request('judul', '');

        if(request()->has('chart-view')){
            $chart = $this->chart();
            return view('keluarga.index', compact('filters', 'judul', 'chart'));
        }

        $chart = [];

        return view('keluarga.index', compact('filters', 'judul', 'chart'));
    }

    public function chart()
    {
        return [
            'kategori' => Str::slug(strtolower(request()->tipe)),
            'nama' => request()->nama,
            'view' => true,
        ];

    }

    /**
     * Display the specified resource.
     *
     * @param number $no_kk
     */
    public function show($no_kk)
    {
        return view('keluarga.detail', compact('no_kk'));
    }

    public function cetak(Request $request)
    {
        return view('keluarga.cetak', ['filter' => $request->getQueryString()]);
    }
}
