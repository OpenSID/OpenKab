<?php

namespace App\Http\Controllers\Api;

use App\Models\Bantuan;
use App\Models\Enums\SasaranEnum;
use App\Http\Controllers\Api\Controller;
use App\Http\Repository\BantuanRepository;
use App\Http\Transformers\BantuanTransformer;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Transformers\StatistikTransformer;
use App\Http\Repository\BantuanPesertaRepository;
use App\Http\Transformers\GrafikBantuanTransformer;
use App\Http\Transformers\BantuanPesertaTransformer;
use Illuminate\Http\Request;

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

    public function cetakBantuan()
    {
        return $this->fractal($this->bantuan->cetakListBantuan(), new BantuanTransformer(), 'daftar bantuan')->respond();
    }

    public function sasaran()
    {
        return response()->json([
            'success' => true,
            'data' =>  SasaranEnum::object(),
        ], Response::HTTP_OK);
    }

    public function tahun()
    {
        return response()->json([
            'success' => true,
            'data' =>  Bantuan::tahun()->first(),
        ], Response::HTTP_OK);
    }

}
