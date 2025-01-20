<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\OpendkSynchronizeRepository;
use App\Http\Transformers\OpendkSynchronizeTransformer;
use App\Http\Transformers\SettingTransformer;
use App\Models\OpendkSynchronize;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class OpendkSynchronizeController extends Controller
{
    public function __construct(protected OpendkSynchronizeRepository $opendk)
    {
    }

    public function index()
    {
        return $this->fractal($this->opendk->listSinkronisasi(), new OpendkSynchronizeTransformer(), 'daftar sinkronisasi')->respond();
    }

    public function getData(Request $request)
    {
        \Log::error("message ". auth()->user());
        $namaKecamatan = $request->get('nama_kecamatan');    
        $kodeKecamatan = $request->get('kode_kecamatan');    
        OpendkSynchronize::upsert([
            ['nama_kecamatan' => $namaKecamatan, 'kode_kecamatan' => $kodeKecamatan],
        ], ['kode_kecamatan']);        
        return response()->json([            
            'message' => 'Berhasil mengambil data sinkronisasi',
        ], Response::HTTP_OK);;
    }
}
