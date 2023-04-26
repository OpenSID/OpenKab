<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\RtmRepository;

class RtmController extends Controller
{
    public function __construct(protected RtmRepository $rtm)
    {
    }

    public function index()
    {
        // return $this->fractal($this->rtm->listRtm(), new RtmTransformer(), 'rtm')->respond();
    }
}
