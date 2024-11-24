<?php

namespace App\Http\Controllers;

class DataPokokController extends Controller
{
    public function pendidikan()
    {
        // dd(session('kabupaten.kode_kabupaten'),session('kecamatan.kode_kecamatan'), session('desa.id'), session()->all());
        return view('pendidikan.index');
    }
}