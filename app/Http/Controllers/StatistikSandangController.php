<?php

namespace App\Http\Controllers;

class StatistikSandangController extends Controller
{
    public function index()
    {
        return view('presisi.statistik.sandang', [
            'detailLink' => url(''),
            'judul' => 'Sandang'            
        ]);
    }    
}
