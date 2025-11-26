<?php

namespace App\Http\Controllers;

class StatistikAdatController extends Controller
{
    public function index()
    {
        return view('presisi.statistik.adat', [
            'detailLink' => url(''),
            'judul' => 'Adat'            
        ]);
    }    
}
