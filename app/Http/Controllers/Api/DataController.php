<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\PendudukRepository;
use App\Http\Transformers\PendudukTransformer;
use App\Http\Transformers\DtksTransformer;

class DataController extends Controller
{
    public function __construct(
        protected PendudukRepository $penduduk
    ) 
    {
    }

    public function kesehatan()
    {
        return $this->fractal($this->penduduk->listPendudukKesehatan(), new PendudukTransformer, 'kesehatan')->respond();
    }

    public function jaminan_kesehatan()
    {
        return $this->fractal($this->penduduk->listPendudukJaminanKesehatan(), new DtksTransformer, 'jaminan-kesehatan')->respond();
    }
}
