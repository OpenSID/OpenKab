<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;

class BantuanKabupatenController extends Controller
{
    protected $permission = 'master-data-bantuan';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $listPermission = $this->generateListPermission();

        return view('master.bantuan.index')->with($listPermission);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('master.bantuan.create');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('master.bantuan.edit', compact('id'));
    }
}
