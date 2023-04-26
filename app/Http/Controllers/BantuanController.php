<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BantuanController extends Controller
{
    public function index()
    {
        return view('bantuan.index');
    }

    public function show($id)
    {
        return view('bantuan.show', compact('id'));
    }

    public function cetak(Request $request)
    {
        $filter = array_filter($request->all());

        return view('bantuan.cetak', compact('filter'));
    }
}
