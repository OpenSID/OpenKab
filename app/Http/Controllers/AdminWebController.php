<?php

namespace App\Http\Controllers;

class AdminWebController extends Controller
{
    public function kategori_index()
    {
        return view('master.kategori.index');
    }

    public function kategori_show($id)
    {
        return view('master.kategori.show', compact('id'));
    }
}
