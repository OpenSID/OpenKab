<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Penduduk;

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
}
