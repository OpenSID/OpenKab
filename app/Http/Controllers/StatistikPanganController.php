<?php

namespace App\Http\Controllers;

class StatistikPanganController extends Controller
{
    public function index()
    {
        return view('presisi.statistik.pangan', [
            'detailLink' => url(''),
            'judul' => 'Pangan'            
        ]);
    }    
}
