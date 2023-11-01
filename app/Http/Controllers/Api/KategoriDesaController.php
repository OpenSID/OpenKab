<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\KategoriDesaRepository;
use App\Http\Transformers\DesaAktifTransformer;

class KategoriDesaController extends Controller
{
    public function __construct(protected KategoriDesaRepository $desa)
    {
    }

    public function index()
    {
        return $this->fractal($this->desa->aktif(), new DesaAktifTransformer(), 'daftar desa teraktif')->respond();
    }
}
