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
        if ($path && $path->logo) {
            $storagePath = storage_path('app/public/img/'.$path->logo);
            if (file_exists($storagePath)) {
                return ambilBerkas($storagePath);
            }

            $publicPath = public_path('storage/img/'.$path->logo);
            if (file_exists($publicPath)) {
                return ambilBerkas($publicPath);
            }
        }

        return ambilBerkas(public_path('assets/img/opensid_logo.png'));
    }
}
