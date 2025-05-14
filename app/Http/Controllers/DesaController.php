<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DesaController extends Controller
{

    public function index()
    {
        $filters = request('filter', [
            'kode_kabupaten' => null,
            'kode_desa' => null,
            'kode_kecamatan' => null,
        ]);
        return view('desa.index', compact('filters'));
    }

    public function cetak(Request $request)
    {
        return view('desa.cetak', ['filter' => $request->getQueryString()]);
    }
}
