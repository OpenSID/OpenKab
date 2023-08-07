<?php

namespace App\Http\Controllers;

class GroupController extends Controller
{
    private $nama_aplikasi;

    public function __construct()
    {
        $this->nama_aplikasi = 'Group '.config('app.namaAplikasi');
    }

    public function index()
    {
        return view('group.index', [
            'nama_aplikasi' => $this->nama_aplikasi,
        ]);
    }

    public function create()
    {
        return view('group.form');
    }

    public function edit($id)
    {
        return view('group.form', ['id' => $id]);
    }
}
