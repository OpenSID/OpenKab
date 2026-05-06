<?php

namespace App\Http\Controllers;

class StatistikSandangController extends Controller
{
    public function index()
    {
        return view('presisi.statistik.sandang', [
            'detailLink' => url('data-presisi/sandang/detail_data'),
            'judul' => 'Sandang',
        ]);
    }
}
