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
        return $this->fractal($this->bantuan->listBantuan(), new BantuanTransformer(), 'daftar bantuan')->respond();
    }

    public function show()
    {
        return $this->fractal($this->bantuan->showBantuan(), new BantuanTransformer(), 'detail bantuan')->respond();
    }
}
