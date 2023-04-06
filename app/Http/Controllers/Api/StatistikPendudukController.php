<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Repository\StatistikPendudukRepository;
use App\Http\Transformers\StatistikPendudukTransformer;
use App\Http\Transformers\GrafikStatistikPendudukTransformer;

class StatistikPendudukController extends Controller
{
    public function __construct(protected StatistikPendudukRepository $bantuan)
    {
    }

    public function index()
    {
        return $this->fractal($this->bantuan->listBantuan(), new StatistikPendudukTransformer(), 'bantuan')->respond();
    }

    public function grafik()
    {
        return $this->fractal($this->bantuan->listBantuan(), new GrafikStatistikPendudukTransformer(), 'grafik')->respond();
    }
}
