<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetailDataJaminanSosialRequest;
use Illuminate\View\View;

class DataPresisiJaminanSosialController extends Controller
{    
    public function detailData(DetailDataJaminanSosialRequest $request): View
    {
        $title = $request->filled('judul') ? htmlspecialchars(strip_tags($request->input('judul'))) : '';
        $colomn = '';

        $filter = $request->input('filter');
        if (isset($filter['tipe'], $filter['nilai']) && $filter['tipe'] !== '' && $filter['nilai'] !== '') {
            $colomn = $filter['tipe'] . ':' . $filter['nilai'];
        }

        return view('data_pokok.data_presisi.jaminan_sosial.detail_data', [
            'title' => $title,
            'colomn' => $colomn,
        ]);
    }    
}