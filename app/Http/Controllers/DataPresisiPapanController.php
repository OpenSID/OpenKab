<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DataPresisiPapanController extends Controller
{
    /**
     * Display detail data presisi papan dengan filter opsional.
     *
     * @param Request $request HTTP request instance     
     * @return View View detail data dengan DataTables
     */
    public function detailData(Request $request): View
    {
        $colomn = '';
        $title = 'Data Presisi Papan - ' . $request->input('judul');
        $title = htmlspecialchars(strip_tags($title), ENT_QUOTES, 'UTF-8');
        $filter = $request->input('filter');

        if (isset($filter['tipe'], $filter['nilai']) && $filter['tipe'] && $filter['nilai']) {
            $colomn = $filter['tipe'] . ':' . $filter['nilai'];
        }

        return view('data_pokok.data_presisi.papan.detail_data', compact('title', 'colomn'));
    }

    public function cetak(Request $request): View
    {
        return view('data_pokok.data_presisi.papan.cetak', ['filter' => $request->getQueryString()]);
    }

     public function detail(Request $request): View
    {
        $data = json_decode($request->data);

        return view('data_pokok.data_presisi.papan.detail', ['data' => $data]);
    }
}
