<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KeluargaController extends Controller
{
    public function index()
    {
        $filters = request('filter', [
            'kode_kabupaten' => null,
            'kode_desa' => null,
            'kode_kecamatan' => null,
        ]);

        return view('keluarga.index', compact('filters'));
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
