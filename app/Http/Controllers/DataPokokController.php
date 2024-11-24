<?php

namespace App\Http\Controllers;

class DataPokokController extends Controller
{
    public function ketenagakerjaan()
    {
        $title = 'Data Pekerjaan dan Pelatihan';
        return view('ketenagakerjaan.index', ['title' => $title]);
    }
}