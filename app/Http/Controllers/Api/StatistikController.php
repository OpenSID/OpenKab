<?php

namespace App\Http\Controllers\Api;

use App\Http\Repository\BantuanRepository;
use App\Http\Repository\KeluargaRepository;
use App\Http\Repository\PendudukRepository;
use App\Http\Repository\RtmRepository;
use App\Http\Repository\StatistikRepository;
use App\Http\Transformers\StatistikTransformer;
use Illuminate\Http\Response;

class StatistikController extends Controller
{
    protected $statistik;

    protected $kategori;

    public function __construct(StatistikRepository $statistik)
    {
        $this->statistik = $statistik;
        $this->kategori = request()->input('filter')['id'] ?? null;
    }

    public function kategoriStatistik()
    {
        if ($this->kategori) {
            return response()->json([
                'success' => true,
                'data' => $this->statistik->getKategoriStatistik($this->kategori),
            ], Response::HTTP_OK);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kategori tidak ditemukan',
        ], Response::HTTP_NOT_FOUND);
    }

    public function penduduk(PendudukRepository $penduduk)
    {
        if ($this->kategori) {
            return $this->fractal($this->statistik->getStatistik($penduduk->listStatistik($this->kategori)), new StatistikTransformer(), 'statistik-penduduk')->respond();
        }

        return response()->json([
            'success' => false,
            'message' => 'Kategori tidak ditemukan',
        ], Response::HTTP_NOT_FOUND);
    }

    public function refTahunPenduduk(PendudukRepository $penduduk)
    {
        return response()->json([
            'success' => true,
            'data' => $penduduk->listTahun(),
        ], Response::HTTP_OK);
    }

    public function refTahunBantuan(BantuanRepository $bantuan)
    {
        return response()->json([
            'success' => true,
            'data' => $bantuan->tahun(),
        ], Response::HTTP_OK);
    }

    public function keluarga(KeluargaRepository $keluarga)
    {
        if ($this->kategori) {
            return $this->fractal($this->statistik->getStatistik($keluarga->listStatistik($this->kategori)), new StatistikTransformer(), 'statistik-keluarga')->respond();
        }

        return response()->json([
            'success' => false,
            'message' => 'Kategori tidak ditemukan',
        ], Response::HTTP_NOT_FOUND);
    }

    public function refTahunKeluarga(KeluargaRepository $keluarga)
    {
        return response()->json([
            'success' => true,
            'data' => $keluarga->listTahun(),
        ], Response::HTTP_OK);
    }

    public function rtm(RtmRepository $rtm)
    {
        return $this->fractal($this->statistik->getStatistik($rtm->listStatistik($this->kategori)), new StatistikTransformer(), 'statistik-rtm')->respond();
    }

    public function refTahunRtm(RtmRepository $rtm)
    {
        return response()->json([
            'success' => true,
            'data' => $rtm->listTahun(),
        ], Response::HTTP_OK);
    }

    public function bantuan(BantuanRepository $bantuan)
    {
        if ($this->kategori) {
            return $this->fractal($this->statistik->getStatistik($bantuan->listStatistik($this->kategori)), new StatistikTransformer(), 'statistik-bantuan')->respond();
        }

        return response()->json([
            'success' => false,
            'message' => 'Kategori tidak ditemukan',
        ], Response::HTTP_NOT_FOUND);
    }
}
