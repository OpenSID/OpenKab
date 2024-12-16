<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DTKSController extends Controller
{
    public function index()
    {
        return view('dtks.papan.index');
    }

    public function cetak(Request $request)
    {
        return view('dtks.papan.cetak', ['filter' => $request->getQueryString()]);
    }
}
