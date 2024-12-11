<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\KelembagaanRepository;
use App\Http\Transformers\KelembagaanPendudukTransformer;
use App\Http\Transformers\KelembagaanTransformer;

class KelembagaanController extends Controller
{
    public function __construct(protected KelembagaanRepository $prasarana)
    {
    }

    public function kelembagaan()
    {
        $kelembagaanRepository = resolve(KelembagaanRepository::class);

        return $this->fractal($this->prasarana->index(), new KelembagaanTransformer($kelembagaanRepository), 'kelembagaan')->toArray();
    }

    public function kelembagaan_penduduk()
    {
        return $this->fractal($this->prasarana->penduduk(), new KelembagaanPendudukTransformer(), 'penduduk')->toArray();
    }
}
