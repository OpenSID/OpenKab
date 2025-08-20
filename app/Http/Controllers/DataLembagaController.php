<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LembagaController extends Controller
{
    public function index()
    {
        $title = 'Data Lembaga';

        return view('lembaga.index', compact('title'));
    }

    public function detail(Request $request)
    {
        $data = json_decode($request->data);

        return view('lembaga.detail', ['data' => $data]);
    }

    public function cetak(Request $request)
    {
        return view('lembaga.cetak', ['filter' => $request->getQueryString()]);
    }
}
