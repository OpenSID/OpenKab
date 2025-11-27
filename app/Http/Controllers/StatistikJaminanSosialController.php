<?php

namespace App\Http\Controllers;

class StatistikJaminanSosialController extends Controller
{
    public function index()
    {
        return view('presisi.statistik.jaminan-sosial', [
            'detailLink' => url(''),
            'judul' => 'Jaminan Sosial'            
        ]);
    }    
}
