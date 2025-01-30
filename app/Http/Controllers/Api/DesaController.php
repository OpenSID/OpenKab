<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\DesaRepository;
use App\Http\Transformers\DesaTransformer;

class DesaController extends Controller
{
    public function __construct(protected DesaRepository $desa)
    {
    }

    public function index()
    {
        return $this->fractal($this->desa->list(), new DesaTransformer(), 'daftar desa')->respond();
    }

    public function all($kec = '')
    {
        return $this->fractal($this->desa->all($kec), new DesaTransformer(), 'daftar desa')->respond();
    }
}
