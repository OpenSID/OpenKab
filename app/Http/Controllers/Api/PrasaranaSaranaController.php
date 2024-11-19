<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\PrasaranaSaranaRepository;
use App\Http\Transformers\PrasaraSaranaTransformer;

class PrasaranaSaranaController extends Controller
{
    public function __construct(protected PrasaranaSaranaRepository $prasarana)
    {
    }

    public function prasaranaSarana()
    {
        return $this->fractal($this->prasarana->index(), new PrasaraSaranaTransformer(), 'prasarana-sarana')->toArray();
    }
}
