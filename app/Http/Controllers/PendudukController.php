<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
class PendudukController extends Controller
{
    public function index()
    {
        return view('penduduk.index');
    }

    public function show($id)
    {
        return view('penduduk.detail', compact('id'));
    }
}
