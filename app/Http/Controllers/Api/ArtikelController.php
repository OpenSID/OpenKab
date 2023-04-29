<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\ArtikelRepository;
use App\Models\Artikel;
use App\Models\Config;
use App\Models\Kategori;
use Symfony\Component\HttpFoundation\Response;

class ArtikelController extends Controller
{
    public function index(ArtikelRepository $artikel)
    {
        return response()->json([
            'data' => $artikel->listArtikel(),
            'message' => 'Berhasil mengambil data artikel',
        ], \Illuminate\Http\Response::HTTP_OK);
    }

    public function nama_desa()
    {
        return response()->json([
            'success' => true,
            'data' => Config::select('id', 'nama_desa')->get(),
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
        ], Response::HTTP_OK);
    }
}
