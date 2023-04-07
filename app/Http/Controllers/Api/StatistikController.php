<?php

namespace App\Http\Controllers\Api;

use App\Models\Rtm;
use App\Http\Repository\RtmRepository;
use App\Http\Controllers\Api\Controller;
use App\Http\Repository\StatistikRepository;
use App\Http\Transformers\StatistikTransformer;

class StatistikController extends Controller
{
    protected $rtm;
    protected $statistik;

    public function __construct(RtmRepository $rtm, StatistikRepository $statistik)
    {
        $this->rtm = $rtm;
        $this->statistik = $statistik;
    }

    public function rtm()
    {
        return $this->fractal($this->statistik->getStatistik([], $this->rtm->listStatistik()), new StatistikTransformer(), 'grafik')->respond();
    }
}
