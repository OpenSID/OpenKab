<?php

namespace App\Http\Controllers;

use App\Http\Repository\PendudukRepository;

class StatistikController extends Controller
{
    public function penduduk(PendudukRepository $pendudukRepository)
    {
        return view('statistik.index', [
            'judul' => 'Penduduk',
            'kategori' => 'penduduk',
        ]);
    }

    public function keluarga()
    {
        return view('statistik.index', [
            'judul' => 'Keluarga',
            'kategori' => 'keluarga',
        ]);
    }

    public function rtm()
    {
        return view('statistik.index', [
            'judul' => 'RTM',
            'kategori' => 'rtm',
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
            'kategori' => 'bantuan',
        ]);
    }

    public function cetak($kategori, $id)
    {
        return view('statistik.cetak', [
            'kategori' => $kategori,
            'id' => $id,
        ]);
    }

}
