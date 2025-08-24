<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LembagaController extends Controller
{
    public function index()
    {
        $title = 'Data Kelembagaan';

        return view('lembaga.index', compact('title'));
    }

    public function cetak(Request $request)
    {
        return view('lembaga.cetak', ['filter' => $request->getQueryString()]);
    }
}
