<?php

namespace App\Http\Controllers;

class DataPokokController extends Controller
{
    public function kesehatan()
    {
        $title = 'Data Kependudukan dan Statistik';

        return view('data_pokok.kesehatan.index', ['title' => $title]);
    }
}
