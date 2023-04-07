<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Repository\KeluargaRepository;
use App\Http\Transformers\KeluargaSejahteraTransformer;
use App\Models\KeluargaSejahtera;

class KeluargaController extends Controller
{
    public function __construct(protected KeluargaRepository $keluargaSejahtera)
    {
    }

    public function index()
    {
        $keluarga = KeluargaSejahtera::first();
        return $this->fractal($this->keluargaSejahtera->kelasSosial(), new KeluargaSejahteraTransformer, 'keluarga')->addMeta([
            'statistik' => $keluarga->statistik,
        ])->respond();
    }
}
