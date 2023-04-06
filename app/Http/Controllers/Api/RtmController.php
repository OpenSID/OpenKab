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
        $data = collect($this->statistik->getStatistik(
            [
                'jumlah' => 10,
                'laki_laki' => 5,
                'perempuan' => 5,
        ],
            [
                'jumlah' => 20,
                'laki_laki' => 15,
                'perempuan' => 5,
            ],
        ))->map(function ($item, $key) {
            $item['id'] = $key + 1;
            return $item;
        });

        return $this->fractal($data, new StatistikTransformer(), 'grafik')->respond();
    }
}
