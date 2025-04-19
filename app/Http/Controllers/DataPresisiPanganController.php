<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataPresisiPanganController extends Controller
{
    public function index()
    {
        $title = 'Data Presisi Pangan';

        return view('data_pokok.data_presisi.pangan.index', compact('title'));
    }

    public function detail(Request $request)
    {
        $data = json_decode($request->data);

        return view('data_pokok.data_presisi.pangan.detail', ['data' => $data]);
    }

    public function cetak(Request $request)
    {
        return view('data_pokok.data_presisi.pangan.cetak', ['filter' => $request->getQueryString()]);
    }
}
