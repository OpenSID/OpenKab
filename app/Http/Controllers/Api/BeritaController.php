<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Repository\BeritaRepository;
use App\Http\Transformers\BeritaTransformer;

class BeritaController extends Controller
{
    public function __construct(protected BeritaRepository $berita)
    {
    }

    public function index()
    {
        return $this->fractal($this->berita->listBerita(), new BeritaTransformer(), 'daftar berita')->respond();
    }
}
