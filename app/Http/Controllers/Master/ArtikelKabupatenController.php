<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ArtikelKabupatenController extends Controller
{
    protected $permission = 'master-data-artikel';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $listPermission = $this->generateListPermission();
        $clearCache = request('clear_cache', false);
        if ($clearCache) {
            (new \App\Services\ArtikelService)->clearCache('artikel', ['filter[id]' => $clearCache]);
        }

        return view('master.artikel.index')->with($listPermission);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        return view('master.artikel.create');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($id): View
    {
        return view('master.artikel.edit', compact('id'));
    }
}
