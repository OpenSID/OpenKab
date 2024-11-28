<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataPokokController extends Controller
{
    public function pariwisata()
    {
        $title = 'Data Potensi Wita dan Sumber Daya';

        return view('pariwisata.index', ['title' => $title]);
    }
}
