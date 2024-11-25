<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\PariwisataRepository;
use App\Http\Transformers\PariwisataTransformer;

class PariwisataController extends Controller
{
    public function __construct(
        protected PariwisataRepository $pariwisata
    ) {
    }

    public function __invoke()
    {
        return $this->fractal($this->pariwisata->listPariwisataKomoditas(), new PariwisataTransformer, 'pariwisata')->respond();
    }
}
