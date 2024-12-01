<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\PendudukRepository;
use App\Http\Transformers\DtksTransformer;
use App\Http\Transformers\KesehatanTransformer;
use App\Http\Transformers\PendudukProdeskelTransformer;

class DataController extends Controller
{
    public function __construct(
        protected PendudukRepository $penduduk
    ) {
    }

    public function kesehatan()
    {
        return $this->fractal($this->penduduk->listPendudukKesehatan(), new KesehatanTransformer, 'kesehatan')->respond();
    }

    public function jaminanSosial()
    {
        return $this->fractal($this->penduduk->listPendudukJaminanSosial(), new DtksTransformer, 'jaminan-sosial')->respond();
    }

    public function pendudukPotensiKelembagaan()
    {
        return $this->fractal($this->penduduk->listPendudukProdeskel(), new PendudukProdeskelTransformer, 'penduduk-prodeskel')->respond();
    }
}
