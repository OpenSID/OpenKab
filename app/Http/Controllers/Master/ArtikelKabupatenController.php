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
        $clearAllCache = $request->query('clear_all_cache', 0);
        
        if ($clearAllCache > 0) {       
            // setiap ada perubahan di clear cache semua, termasuk ketika edit karena bisa jadi edit judul saja     
            (new ArtikelService)->clearAllCache();
        }

        return view('master.artikel.index')->with($listPermission);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $listPermission = $this->generateListPermission();

        return view('master.artikel.create')->with($listPermission);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        return view('master.artikel.edit', compact('id'));
    }
}
