<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\ArtikelRepository;
use App\Http\Transformers\ArtikelTransformer;
use App\Models\Artikel;
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

    public function tahun()
    {
        return response()->json([
            'success' => true,
            'data' => Artikel::tahun()->first(),
        ], Response::HTTP_OK);
    }
}
