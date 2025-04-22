<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataPresisiSeniBudayaController extends Controller
{
    public function index()
    {
        $title = 'Data Presisi Seni Budaya';

        return view('data_pokok.data_presisi.seni_budaya.index', compact('title'));
    }

    public function detail(Request $request)
    {
        $data = json_decode($request->data);

        return view('data_pokok.data_presisi.seni_budaya.detail', ['data' => $data]);
    }

    public function cetak(Request $request)
    {
        return view('data_pokok.data_presisi.seni_budaya.cetak', ['filter' => $request->getQueryString()]);
    }
}
