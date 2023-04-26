<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\BantuanPesertaRepository;
use App\Http\Repository\BantuanRepository;
use App\Http\Transformers\BantuanPesertaTransformer;
use App\Http\Transformers\BantuanTransformer;

class BantuanController extends Controller
{
    public function __construct(protected BantuanRepository $bantuan, protected BantuanPesertaRepository $bantuanPeserta)
    {
    }

    public function index()
    {
        return $this->fractal($this->bantuan->listBantuan(), new BantuanTransformer(), 'daftar bantuan')->respond();
    }

    public function peserta()
    {
        return $this->fractal($this->bantuanPeserta->listBantuanPeserta(), new BantuanPesertaTransformer(), 'peserta bantuan')->respond();
    }
}
