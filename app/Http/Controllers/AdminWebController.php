<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminWebController extends Controller
{
    public function kategori_index()
    {
        return view('admin_web.kategori.index');
    }

    public function kategori_show($id)
    {
        return view('admin_web.kategori.show', compact('id'));
    }
}
