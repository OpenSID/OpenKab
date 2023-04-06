<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\RtmRepository;
use App\Http\Controllers\Api\Controller;
use App\Http\Repository\StatistikRepository;
use App\Http\Transformers\GrafikRtmTransformer;
use App\Http\Transformers\StatistikTransformer;

class RtmController extends Controller
{
    public function __construct(protected RtmRepository $rtm, protected StatistikRepository $statistik)
    {
    }

    public function index()
    {
        // return $this->fractal($this->rtm->listRtm(), new RtmTransformer(), 'rtm')->respond();
    }

    public function grafik()
    {
        return $this->fractal($this->statistik->getStatistik([], []), new StatistikTransformer(), 'grafik')->respond();
    }
}
