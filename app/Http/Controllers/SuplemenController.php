<?php

namespace App\Http\Controllers;

class SuplemenController extends Controller
{
    public function index()
    {
        $list_sasaran = unserialize(SASARAN);

        return view('suplemen.index', compact('list_sasaran'));
    }

    public function form()
    {
        $list_sasaran = unserialize(SASARAN);
        $attributes = unserialize(ATTRIBUTES);

        return view('suplemen.form', compact('list_sasaran', 'attributes'));
    }
}
