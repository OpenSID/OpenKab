<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SuplemenController extends Controller
{
    public function form()
    {
        $list_sasaran = unserialize(SASARAN);
        $attributes = unserialize(ATTRIBUTES);

        return view('suplemen.form', compact('list_sasaran', 'attributes'));
    }
}
