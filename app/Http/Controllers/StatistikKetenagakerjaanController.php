<?php

namespace App\Http\Controllers;

class StatistikKetenagakerjaanController extends Controller
{
    public function index()
    {
        return view('presisi.statistik.ketenagakerjaan', [
            'detailLink' => url(''),
            'judul' => 'Ketenagakerjaan'            
        ]);
    }    
}
