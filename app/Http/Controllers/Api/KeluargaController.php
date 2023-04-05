<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Repository\KeluargaRepository;
use App\Http\Transformers\KeluargaTransformer;

class KeluargaController extends Controller
{
    public function __construct(protected KeluargaRepository $keluarga)
    {
    }

    public function index()
    {
        return $this->fractal($this->keluarga->listKeluarga(), new KeluargaTransformer, 'keluarga')->respond();
    }
}
