<?php

namespace App\Http\Controllers;

class KeluargaController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param number $no_kk
     */
    public function show($no_kk)
    {
        return view('keluarga.detail', compact('no_kk'));
    }
}
