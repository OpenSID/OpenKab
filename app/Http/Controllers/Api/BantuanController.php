<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\BantuanRepository;

class BantuanController extends Controller
{
    public function __construct(protected BantuanRepository $bantuan)
    {
    }

    public function __invoke()
    {
        return $this->fractal($this->bantuan->listBantuan(), new BantuanTransformer, 'bantuan')->respond();
    }
}
