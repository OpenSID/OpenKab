<?php

namespace App\Http\Controllers;

class AdminWebController extends Controller
{
    protected $permission = 'master-data-artikel';

    public function kategori_index($parent)
    {
        $listPermission = $this->generateListPermission();

        return view('master.kategori.index')->with($listPermission);
    }

    public function kategori_aksi()
    {
        return view('master.kategori.aksi');
    }

    public function kategori_create($id)
    {
        return view('master.kategori.create');
    }

    public function kategori_edit()
    {
        return view('master.kategori.edit');
    }

    public function pengaturan_index()
    {
        return view('master.pengaturan.index');
    }
}
