<?php

namespace App\Http\Controllers\Api;

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

    public function desa()
    {
        return $this->fractal($this->wilayah->listDesa(), function ($wilayah) {
            return $wilayah->toArray();
        }, 'desa')->respond();
    }

    public function penduduk()
    {
        return $this->fractal($this->wilayah->listTotalPenduduk(), function ($wilayah) {
            $wilayah->penduduk_count = angka_lokal($wilayah->penduduk_count);

            return $wilayah->toArray();
        }, 'desa')->respond();
    }
}
