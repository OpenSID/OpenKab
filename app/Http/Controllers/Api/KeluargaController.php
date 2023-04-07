<?php

namespace App\Http\Controllers\Api;

use App\Models\KeluargaSejahtera;
use App\Http\Controllers\Api\Controller;
use App\Http\Repository\KeluargaRepository;
use App\Http\Transformers\GrafikKeluargaTransformer;
use App\Http\Transformers\KeluargaSejahteraTransformer;

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

    public function grafik()
    {
        return $this->fractal($this->keluargaSejahtera->kelasSosial(), new GrafikKeluargaTransformer(), 'grafik')->respond();
    }
}
