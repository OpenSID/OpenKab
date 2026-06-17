<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class StatistikAktivitasKeagamaanController extends Controller
{
    public function index(): View
    {
        return view('presisi.statistik.aktivitas-keagamaan', [
            'detailLink' => url('data-presisi/aktivitas-keagamaan/detail_data'),
            'judul' => 'Aktivitas Keagamaan'
        ]);
    }    
}
