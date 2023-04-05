<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Repository\BantuanRepository;
use App\Http\Transformers\BantuanTransformer;
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
        return $this->fractal($this->bantuan->listBantuan(), new GrafikBantuanTransformer(), 'grafik')->respond();
    }
}
