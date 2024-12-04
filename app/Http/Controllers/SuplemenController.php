<?php

namespace App\Http\Controllers;

use App\Models\Suplemen;
use App\Models\Wilayah;

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

    public function detail($id)
    {
        $sasaran = unserialize(SASARAN);
        $suplemen = Suplemen::findOrFail($id);
        $wilayah = Wilayah::treeAccess();

        return view('suplemen.detail', compact('id', 'sasaran', 'suplemen', 'wilayah'));
    }
}
