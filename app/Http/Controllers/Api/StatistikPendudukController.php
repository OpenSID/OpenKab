<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Repository\StatistikPendudukRepository;
use App\Http\Transformers\StatistikPendudukTransformer;
use App\Http\Transformers\GrafikStatistikPendudukTransformer;

class StatistikPendudukController extends Controller
{
    public function __construct(protected StatistikPendudukRepository $penduduk)
    {
    }

    public function index()
    {
        return $this->fractal($this->penduduk->listPenduduk(), new StatistikPendudukTransformer(), 'penduduk')->respond();
    }

    public function grafik()
    {
        return $this->fractal($this->penduduk->listPenduduk(), new GrafikStatistikPendudukTransformer(), 'grafik')->respond();
    }
}
