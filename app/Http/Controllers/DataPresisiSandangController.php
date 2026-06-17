<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DataPresisiSandangController extends Controller
{    
    public function detailData(Request $request): View
    {
        $colomn = '';
        $title = 'Data Presisi Sandang - '.$request->input('judul');
        $title = htmlspecialchars(strip_tags($title), ENT_QUOTES, 'UTF-8');
        $filter = $request->input('filter');

        if (isset($filter['tipe'], $filter['nilai']) && $filter['tipe'] && $filter['nilai']) {
            $colomn = $filter['tipe'].':'.$filter['nilai'];
        }

        return view('data_pokok.data_presisi.sandang.detail_data', compact('title', 'colomn'));
    }
}
