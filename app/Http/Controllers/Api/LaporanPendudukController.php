<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\LaporanPendudukRepository;
use App\Http\Transformers\LaporanPendudukTransform;

class LaporanPendudukController extends Controller
{
    public function __construct(
        protected LaporanPendudukRepository $penduduk
    ) {
    }

    public function index()
    {
        return $this->fractal($this->penduduk->listLaporanPenduduk(), new LaporanPendudukTransform, 'laporan_penduduk')->respond();
    }
}
