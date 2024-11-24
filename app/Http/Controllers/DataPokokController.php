<?php

namespace App\Http\Controllers;

class DataPokokController extends Controller
{
    public function pendidikan()
    {
        return view('pendidikan.index');
    }
}