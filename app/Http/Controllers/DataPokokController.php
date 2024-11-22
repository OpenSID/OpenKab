<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataPokokController extends Controller
{
    public function pariwisata()
    {
        return view('pariwisata.index');
    }
}
