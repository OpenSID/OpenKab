<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StatistikController extends Controller
{
    public function index()
    {
        // return view('statistik.bantuan');
    }

    public function bantuan()
    {
        return view('statistik.bantuan.index');
    }

    public function cetak_bantuan($tanggal = null)
    {
        return view('statistik.bantuan.cetak');
    }
}
