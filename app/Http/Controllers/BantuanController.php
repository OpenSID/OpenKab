<?php

namespace App\Http\Controllers;

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
}
