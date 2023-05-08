<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\ArtikelRepository;
use App\Http\Transformers\ArtikelTransformer;
use App\Models\Artikel;
<<<<<<< HEAD
use App\Models\Config;
use App\Models\Kategori;
=======
>>>>>>> 9d877b828ce1bb099dda6c8a36f21dd6716748d8
use Symfony\Component\HttpFoundation\Response;

class ArtikelController extends Controller
{
    public function __construct(protected ArtikelRepository $artikel)
    {
    }

    public function index()
    {
        return $this->fractal($this->artikel->listArtikel(), new ArtikelTransformer(), 'daftar artikel')->respond();
    }

<<<<<<< HEAD
    public function nama_desa()
    {
        return response()->json([
            'success' => true,
            'data' => Config::select('nama_desa')->get(),
        ], Response::HTTP_OK);
    }

    public function kategori()
    {
        return response()->json([
            'success' => true,
            'data' => Kategori::select('id', 'kategori')->get(),
        ], Response::HTTP_OK);
    }

    public function tahun()
    {
        $tahun_awal = Artikel::orderBy('tgl_upload', 'asc')->first();
        $tahun_akhir = Artikel::orderBy('tgl_upload', 'desc')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'tahun_awal' => date('Y', strtotime($tahun_awal->tgl_upload)),
                'tahun_akhir' => date('Y', strtotime($tahun_akhir->tgl_upload)),
            ],
=======
    public function tahun()
    {
        return response()->json([
            'success' => true,
            'data' => Artikel::tahun()->first(),
>>>>>>> 9d877b828ce1bb099dda6c8a36f21dd6716748d8
        ], Response::HTTP_OK);
    }
}
