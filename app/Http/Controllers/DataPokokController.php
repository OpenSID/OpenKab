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

    public function pendidikan()
    {
        return view('pendidikan.index');
    }

    public function ketenagakerjaan()
    {
        $title = 'Data Pekerjaan dan Pelatihan';

        return view('ketenagakerjaan.index', ['title' => $title]);
    }
}
