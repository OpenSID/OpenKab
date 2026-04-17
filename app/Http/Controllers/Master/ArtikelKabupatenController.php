<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Services\ArtikelService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArtikelKabupatenController extends Controller
{
    protected $permission = 'master-data-artikel';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $listPermission = $this->generateListPermission();
        $clearCache = $request->query('clear_cache', 0);

        if ($clearCache > 0) {
            (new ArtikelService)->clearCacheSingle((int) $clearCache);
        }

        return view('master.artikel.index')->with($listPermission);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('master.artikel.create');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        return view('master.artikel.edit', compact('id'));
    }
}
