<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\DokumenRepository;
use App\Http\Transformers\DokumenTransformer;

class DokumenController extends Controller
{
    public function __construct(protected DokumenRepository $dokumen)
    {
    }

    public function __invoke()
    {
        return $this->fractal($this->dokumen->listDokumen(), new DokumenTransformer, 'dokumen')->respond();
    }
}
