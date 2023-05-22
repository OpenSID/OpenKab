<?php

namespace App\Http\Controllers;

use App\Models\Penduduk;
use Illuminate\Http\Request;

class PendudukController extends Controller
{
    public function index()
    {
        return view('penduduk.index');
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
