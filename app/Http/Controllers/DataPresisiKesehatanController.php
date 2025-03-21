<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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
}
