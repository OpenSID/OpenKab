<?php

namespace App\Http\Controllers;

use App\Models\Identitas;

class IdentitasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('identitas.index');
    }

    public function edit()
    {
        return view('identitas.edit');
    }

    public function logo()
    {
        $path = Identitas::first();
        ambilBerkas(public_path('storage/img/').$path->logo);
    }
}
