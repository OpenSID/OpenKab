<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StatistikController extends Controller
{
    public function penduduk()
    {
        return view('statistik.index', [
            'judul' => 'Penduduk',
        ]);
    }

    public function keluarga()
    {
        return view('statistik.index', [
            'judul' => 'Keluarga',
        ]);
    }

    public function rtm()
    {
        return view('statistik.index', [
            'judul' => 'RTM',
        ]);
    }

    public function cetak_rtm()
    {
        return view('statistik.rtm.cetak');
    }

    public function bantuan()
    {
        return view('statistik.index', [
            'judul' => 'Bantuan',
        ]);
    }

    public function cetak($kategori, $id, Request $request)
    {
        $filter = array_filter($request->all());
     
        return view('statistik.cetak', [
            'kategori' => $kategori,
            'id' => $id,
            'filter' => $filter['filter']
        ]);
    }
}
