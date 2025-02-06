<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\KeuanganRepository;
use App\Http\Transformers\KeuanganTransformer;
use App\Http\Transformers\LaporanSinkronisasiTransformer;

class KeuanganController extends Controller
{
    public function __construct(protected KeuanganRepository $keuangan)
    {
    }

    public function apbdes()
    {
        return $this->fractal($this->keuangan->apbdes(), new KeuanganTransformer(), 'keuangan')->respond();
    }

    public function laporan_apbdes()
    {
        return $this->fractal($this->keuangan->laporan_apbdes(), new LaporanSinkronisasiTransformer(), 'laporan APBDes')->respond();
    }
}
