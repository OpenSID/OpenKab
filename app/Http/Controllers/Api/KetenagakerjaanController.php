<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\PendudukRepository;
use App\Http\Transformers\KetenagakerjaanTransformer;

class KetenagakerjaanController extends Controller
{
    public function __construct(
        protected PendudukRepository $penduduk
    ) {
    }

    public function __invoke()
    {
        return $this->fractal($this->penduduk->listPendudukKetenagakerjaan(), new KetenagakerjaanTransformer, 'ketenagakerjaan')->respond();
    }
}
