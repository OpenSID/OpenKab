<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\Controller;
use App\Http\Repository\WilayahRepository;

class WilayahController extends Controller
{
    public function __construct(protected WilayahRepository $wilayah)
    {
    }

    public function dusun()
    {
        return $this->fractal($this->wilayah->listDusun(), function ($wilayah) {
            return $wilayah->toArray();
        }, 'dusun')->respond();
    }

    public function rw()
    {
        return $this->fractal($this->wilayah->listRW(), function ($wilayah) {
            return $wilayah->toArray();
        }, 'rw')->respond();
    }

    public function rt()
    {
        return $this->fractal($this->wilayah->listRT(), function ($wilayah) {
            return $wilayah->toArray();
        }, 'rt')->respond();
    }
}
