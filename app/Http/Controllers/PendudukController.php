<?php

namespace App\Http\Controllers;

use App\Models\Penduduk;
use Illuminate\Http\Request;

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
        ]);
        return view('penduduk.index', compact('filters'))->with($listPermission);
    }

    public function show(Penduduk $penduduk)
    {
        return view('penduduk.detail', compact('penduduk'));
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
