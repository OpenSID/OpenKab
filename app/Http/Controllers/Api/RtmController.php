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
        $footer = [
            [
                'nama' => 'JUMLAH',
                'jumlah' => 92,
                'laki_laki' => 45,
                'perempuan' => 47,
            ],
            [
                'nama' => 'BELUM MENGISI',
                'jumlah' => 0,
                'laki_laki' => 0,
                'perempuan' => 0,
            ],
            [
                'nama' => 'TOTAL',
                'jumlah' => 92,
                'laki_laki' => 45,
                'perempuan' => 47,
            ]
        ];

        $header = [
            [
                'id' => 1,
                'nama' => 'ISLAM',
                'laki_laki' => 41,
                'perempuan' => 43,
            ],
            [
                'id' => 2,
                'nama' => 'KRISTEN',
                'laki_laki' => 0,
                'perempuan' => 0,
            ],
            [
                'id' => 3,
                'nama' => 'KATHOLIK',
                'laki_laki' => 0,
                'perempuan' => 0,
            ],
            [
                'id' => 4,
                'nama' => 'HINDU',
                'laki_laki' => 3,
                'perempuan' => 4,
            ],
            [
                'id' => 5,
                'nama' => 'BUDHA',
                'laki_laki' => 1,
                'perempuan' => 0,
            ],
            [
                'id' => 6,
                'nama' => 'KHONGHUCU',
                'laki_laki' => 0,
                'perempuan' => 0,
            ],
            [
                'id' => 7,
                'nama' => 'KEPERCAYAAN TERHADAP TUHAN YME / LAINNYA',
                'laki_laki' => 0,
                'perempuan' => 0,
            ]
        ];

        return $this->fractal($this->statistik->getStatistik($header, $footer), new StatistikTransformer(), 'grafik')->respond();
    }
}
