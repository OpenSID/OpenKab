<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LaporanDesaAktifController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Laporan Desa Aktif';
        return view('laporan.desa_aktif.index', compact('title'));
    }    
}
