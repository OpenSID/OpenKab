<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataPresisiAdatController extends Controller
{
    public function index()
    {
        $title = 'Data Presisi Adat';

        return view('data_pokok.data_presisi.adat.index', compact('title'));
    }

    public function detail(Request $request)
    {
        $data = json_decode($request->data);

        return view('data_pokok.data_presisi.adat.detail', ['data' => $data]);
    }

    public function cetak(Request $request)
    {
        return view('data_pokok.data_presisi.adat.cetak', ['filter' => $request->getQueryString()]);
    }
}
