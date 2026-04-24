<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DataPresisiSandangController extends Controller
{    
    public function detailData(): View
    {
        $colomn = '';
        $title = 'Data Presisi Sandang '.request('judul');
        $filter = request('filter');

        if (isset($filter['tipe'], $filter['nilai']) && $filter['tipe'] && $filter['nilai']) {
            $colomn = $filter['tipe'].':'.$filter['nilai'];
        }

        return view('data_pokok.data_presisi.sandang.detail_data', compact('title', 'colomn'));
    }
}
