<?php

namespace App\Http\Controllers;

class StatistikKesehatanController extends Controller
{
    public function index(): \Illuminate\View\View
    {
        return view('presisi.statistik.kesehatan', [
            'detailLink' => url('data-presisi/kesehatan/detail_data'),
            'judul' => 'Kesehatan'
        ]);
    }    
}
