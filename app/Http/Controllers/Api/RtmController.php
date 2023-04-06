<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\RtmRepository;
use App\Http\Controllers\Api\Controller;
use App\Http\Transformers\RtmTransformer;
use App\Http\Transformers\BantuanTransformer;
use App\Http\Transformers\GrafikRtmTransformer;

class RtmController extends Controller
{
    public function __construct(protected RtmRepository $rtm)
    {
    }

    public function index()
    {
        return $this->fractal($this->rtm->listRtm(), new RtmTransformer(), 'rtm')->respond();
    }

    public function grafik()
    {
        return $this->fractal($this->rtm->listRtm(), new GrafikRtmTransformer(), 'grafik')->respond();
    }
}
