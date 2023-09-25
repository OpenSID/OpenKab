<?php

namespace App\Http\Controllers;

class GroupController extends Controller
{
    private $nama_aplikasi;
    protected $permission = 'pengaturan-group';

    public function __construct()
    {
        $this->nama_aplikasi = 'Pengaturan Grup Pengguna';
    }

    public function index()
    {
        $listPermission = $this->generateListPermission();
        return view('group.index', [
            'nama_aplikasi' => $this->nama_aplikasi,
        ])->with($listPermission);
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
