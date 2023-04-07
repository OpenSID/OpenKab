<?php

namespace App\Http\Controllers;

use App\Models\Bantuan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class StatistikController extends Controller
{
    public function index()
    {
        // return view('statistik.bantuan');
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
}
