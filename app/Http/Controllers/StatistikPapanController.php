<?php

namespace App\Http\Controllers;

class StatistikPapanController extends Controller
{
    public function index()
    {
        return view('presisi.statistik.papan', [
            'detailLink' => url(''),
            'judul' => 'Papan'            
        ]);
    }    
}
