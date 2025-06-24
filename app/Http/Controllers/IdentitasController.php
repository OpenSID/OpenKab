<?php

namespace App\Http\Controllers;

use App\Models\Identitas;

class IdentitasController extends Controller
{
    protected $permission = 'pengaturan-identitas';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $listPermission = $this->generateListPermission();

        return view('identitas.index')->with($listPermission);
    }

    public function edit()
    {
        return view('identitas.edit');
    }

    public function logo()
    {
        $path = Identitas::first();
        if (is_null($path) || is_null($path->logo)) {
            $path->logo = 'opensid_logo.png';
        }
        ambilBerkas(public_path('storage/img/').$path->logo);
    }
}
