<?php

namespace App\Http\Controllers;

class StatistikSenibudayaController extends Controller
{
    public function index()
    {
        return view('presisi.statistik.senibudaya', [
            'detailLink' => url(''),
            'judul' => 'seni budaya'            
        ]);
    }    
}
