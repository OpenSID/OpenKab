<?php

namespace App\Http\Controllers;

use App\Models\Team;

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
        $listPermission = $this->generateListPermission();

        return view('group.form')->with($listPermission)->with('isAdmin', false);
    }

    public function edit($id)
    {
        $listPermission = $this->generateListPermission();
        $team = Team::find($id);
        $isAdmin = $team->name == 'administrator' ? true : false;
        
        return view('group.form', ['id' => $id])->with($listPermission)->with('isAdmin', $isAdmin);
    }
}
