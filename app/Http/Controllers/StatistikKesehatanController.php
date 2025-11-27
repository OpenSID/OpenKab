<?php

namespace App\Http\Controllers;

class StatistikKesehatanController extends Controller
{
    public function index()
    {
        return view('presisi.statistik.kesehatan', [
            'detailLink' => url(''),
            'judul' => 'Kesehatan'            
        ]);
    }    
}
