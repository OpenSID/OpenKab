<?php

namespace App\Http\Controllers;

class DataPokokController extends Controller
{
    public function jaminanSosial()
    {
        $title = 'Data Kepesertaan Program dan Statistik';
        return view('jaminan_sosial.index', ['title' => $title]);
    }
}