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

    public function keluarga()
    {
        return view('statistik.keluarga.index', [
            'kategori_statistik' => Keluarga::KATEGORI_STATISTIK,
        ]);
    }

    public function cetak_keluarga()
    {
        return view('statistik.keluarga.cetak');
    }

    public function rtm()
    {
        return view('statistik.rtm.index', [
            'kategori_statistik' => Bantuan::KATEGORI_STATISTIK,
        ]);
    }

    public function cetak_rtm()
    {
        return view('statistik.rtm.cetak');
    }

    public function bantuan()
    {
        return view('statistik.bantuan.index');
    }

    public function cetak_bantuan()
    {
        return view('statistik.bantuan.cetak');
    }

    public function penduduk()
    {
//        dd(Penduduk::KATEGORI_STATISTIK);
        return view('statistik.penduduk.index', [
            'kategori_statistik' => Penduduk::KATEGORI_STATISTIK,
        ]);
    }

    public function cetak_penduduk()
    {
        return view('statistik.penduduk.cetak');
    }
}
