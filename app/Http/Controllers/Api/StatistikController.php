<?php

namespace App\Http\Controllers\Api;

use App\Models\Rtm;
use App\Http\Repository\RtmRepository;
use App\Http\Controllers\Api\Controller;
use App\Http\Repository\BantuanRepository;
use App\Http\Repository\StatistikRepository;
use App\Http\Transformers\StatistikTransformer;

class StatistikController extends Controller
{
    protected $rtm;
    protected $bantuan;
    protected $statistik;

    public function __construct(RtmRepository $rtm, BantuanRepository $bantuan, StatistikRepository $statistik)
    {
        $this->rtm = $rtm;
        $this->bantuan = $bantuan;
        $this->statistik = $statistik;
    }


    public function rtm()
    {
        return $this->fractal($this->statistik->getStatistik($this->rtm->listStatistik()), new StatistikTransformer(), 'statistik-rtm')->respond();
    }

    public function bantuan()
    {
        return $this->fractal($this->statistik->getStatistik($this->bantuan->listStatistik()), new StatistikTransformer(), 'statistik-bantuan')->respond();
    }
}
