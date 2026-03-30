<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Support\Facades\Session;

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
        // IDOR Prevention: Authorization check
        $team = Team::find($id);
        
        if (! $team) {
            Session::flash('error', 'Grup tidak ditemukan');

            return redirect(route('groups.index'));
        }

        $this->authorize('update', $team);

        $listPermission = $this->generateListPermission();
        $isAdmin = $team->name == 'administrator' ? true : false;

        return view('group.form', ['id' => $id])->with($listPermission)->with('isAdmin', $isAdmin);
    }
}
