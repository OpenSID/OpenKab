<?php

namespace App\Http\Controllers;

class StatistikAktivitasKeagamaanController extends Controller
{
    public function index()
    {
        return view('presisi.statistik.aktivitas-keagamaan', [
            'detailLink' => url(''),
            'judul' => 'Aktivitas Keagamaan'            
        ]);
    }    
}
