<?php

namespace App\Http\Controllers;

class DasborController extends Controller
{
    public function index()
    {
        // dd(session('desa.id'));
        // dd(session('kecamatan.kode_kecamatan'));
        return view('dasbor.index');
    }
}
