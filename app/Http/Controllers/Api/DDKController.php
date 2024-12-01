<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\DDKRepository;
use App\Http\Transformers\DDKTransformer;

class DDKController extends Controller
{
    public function __construct(protected DDKRepository $ddk)
    {
    }

    public function pangan()
    {
        return $this->fractal($this->ddk->pangan(), new DDKTransformer(), 'ddk_pangan')->respond();
    }
}
