<?php

namespace App\Http\Controllers;

class StatistikPendidikanController extends Controller
{
    public function index()
    {
        return view('presisi.statistik.pendidikan', [
            'detailLink' => url(''),
            'judul' => 'Pendidikan'            
        ]);
    }    
}
