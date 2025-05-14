<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KecamatanController extends Controller
{
    public function index()
    {
        $filters = request('filter', [
            'kode_kabupaten' => null,
            'kode_desa' => null,
            'kode_kecamatan' => null,
        ]);

        return view('kecamatan.index', compact('filters'));
    }

    public function cetak(Request $request)
    {
        return view('kecamatan.cetak', ['filter' => $request->getQueryString()]);
    }
}
