<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetailDataKesehatanRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DataPresisiKesehatanController extends Controller
{
    public function index()
    {
        $title = 'Data Presisi Kesehatan';

        return view('data_pokok.data_presisi.kesehatan.index', compact('title'));
    }

    public function detail(Request $request)
    {
        $data = json_decode($request->data);

        return view('data_pokok.data_presisi.kesehatan.detail', ['data' => $data]);
    }

    public function cetak(Request $request)
    {
        return view('data_pokok.data_presisi.kesehatan.cetak', ['filter' => $request->getQueryString()]);
    }

    public function detailData(DetailDataKesehatanRequest $request): View
    {
        $title = $request->filled('judul') ? htmlspecialchars(strip_tags($request->input('judul'))) : '';

        $colomn = '';

        $filter = $request->input('filter');
        $tipe = $request->input('tipe');
        $nilai = $request->input('filter.nilai');

        if (is_array($filter) && isset($filter['tipe'], $filter['nilai']) && $filter['tipe'] !== '' && $filter['nilai'] !== '') {
            $colomn = $filter['tipe'] . ':' . $filter['nilai'];
        } elseif ($tipe && $nilai) {
            $colomn = $tipe . ':' . $nilai;
        }

        return view('data_pokok.data_presisi.kesehatan.detail_data', [
            'title' => $title,
            'colomn' => $colomn,
        ]);
    }
}
