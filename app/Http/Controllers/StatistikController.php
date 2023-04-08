<?php

namespace App\Http\Controllers;

use App\Models\Bantuan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Keluarga;
use App\Models\Penduduk;

class StatistikController extends Controller
{
    public function index()
    {
        // return view('statistik.bantuan');
    }

    public function penduduk()
    {
        return view('statistik.index', [
            'judul' => 'Penduduk',
            'kategori' => 'penduduk',
        ]);
    }

    public function cetak_penduduk()
    {
        return view('statistik.penduduk.cetak');
    }

    public function keluarga()
    {
        return view('statistik.index', [
            'judul' => 'Keluarga',
            'kategori' => 'keluarga',
        ]);
    }

    public function cetak_keluarga()
    {
        return view('statistik.keluarga.cetak');
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

    public function cetak_bantuan()
    {
        return view('statistik.bantuan.cetak');
    }
}
