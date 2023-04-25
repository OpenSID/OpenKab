<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\KeluargaRepository;
use App\Http\Transformers\RincianKeluargaTransformer;
use Illuminate\Http\Request;

class KeluargaController extends Controller
{
    public function __construct(protected KeluargaRepository $keluarga)
    {
    }

    public function __invoke()
    {
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request)
    {
        return $this->fractal($this->keluarga->rincianKeluarga($request->no), new RincianKeluargaTransformer(), 'rincian keluarga')->respond();
    }
}
