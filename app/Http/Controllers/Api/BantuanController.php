<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Repository\BantuanRepository;
use App\Http\Transformers\BantuanTransformer;
use App\Http\Transformers\StatistikTransformer;
use App\Http\Transformers\GrafikBantuanTransformer;

class BantuanController extends Controller
{
    public function __construct(protected BantuanRepository $bantuan)
    {
    }

    public function index()
    {
        return $this->fractal($this->bantuan->listBantuan(), new BantuanTransformer(), 'bantuan')->respond();
    }

    public function grafik()
    {
        $footer = $this->bantuan->listBantuan();

        return $this->fractal($this->statistik->getStatistik($header, $footer), new StatistikTransformer(), 'grafik')->respond();
    }
}
