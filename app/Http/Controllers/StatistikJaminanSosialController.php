<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class StatistikJaminanSosialController extends Controller
{
    public function index(): View
    {
        return view('presisi.statistik.jaminan-sosial', [
            'detailLink' => url('data-presisi/jaminan-sosial/detail_data'),
            'judul' => 'Jaminan Sosial'            
        ]);
    }    
}
