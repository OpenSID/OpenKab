<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\PembangunanOpenDKRepository;
use App\Http\Repository\PembangunanRincianOpenDKRepository;
use App\Http\Transformers\PembangunanOpenDKTransformer;
use App\Http\Transformers\PembangunanRincianOpenDKTransformer;

class PembangunanController extends Controller
{
    public function __construct(protected PembangunanOpenDKRepository $pembangunanOpenDK, protected PembangunanRincianOpenDKRepository $pembangunanRincianOpenDK)
    {
    }

    public function syncPembangunanOpenDk()
    {
        return $this->fractal($this->pembangunanOpenDK->listPembangunanSyncOpenDk(), new PembangunanOpenDKTransformer, 'Daftar Pembangunan')->respond();
    }

    public function getPembangunanOpenDk($id)
    {
        return $this->fractal($this->pembangunanOpenDK->getPembangunan($id), new PembangunanOpenDKTransformer, 'Data Pembangunan')->respond();
    }

    public function getPembangunanRincianOpenDk($id, $kode_desa)
    {
        return $this->fractal($this->pembangunanRincianOpenDK->getPembangunanRincian(true, $id, $kode_desa), new PembangunanRincianOpenDKTransformer, 'Rincian Pembangunan')->respond();
    }
}
