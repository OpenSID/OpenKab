<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataPokokController extends Controller
{   
    public function kesehatan()
    {
        $title = 'Data Kependudukan dan Statistik';

        return view('data_pokok.kesehatan.index', ['title' => $title]);
    }

    public function jaminanSosial()
    {
        $title = 'Data Kepesertaan Program dan Statistik';

        return view('jaminan_sosial.index', ['title' => $title]);
    }
    
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
