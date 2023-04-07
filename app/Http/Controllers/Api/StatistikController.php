<?php

namespace App\Http\Controllers\Api;

use App\Models\Rtm;
use App\Models\Umur;
use App\Models\Covid;
use App\Models\Hamil;
use App\Models\Penduduk;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Repository\RtmRepository;
use App\Http\Controllers\Api\Controller;
use App\Http\Repository\BantuanRepository;
use App\Http\Repository\PendudukRepository;
use App\Http\Repository\StatistikRepository;
use App\Http\Transformers\StatistikTransformer;

class StatistikController extends Controller
{
    protected $penduduk;
    protected $rtm;
    protected $bantuan;
    protected $statistik;
    protected $kategori;

    public function __construct(PendudukRepository $penduduk, RtmRepository $rtm, BantuanRepository $bantuan, StatistikRepository $statistik)
    {
        $this->penduduk = $penduduk;
        $this->rtm = $rtm;
        $this->bantuan = $bantuan;
        $this->statistik = $statistik;
        $this->kategori = request()->input('filter')['slug'] ?? null;
    }

    public function penduduk()
    {
        if ($this->kategori) {
            return $this->fractal($this->statistik->getStatistik($this->penduduk->listStatistik($this->kategori)), new StatistikTransformer(), 'statistik-penduduk')->respond();
        }
        return response()->json([
                'success' => false,
                'message' => 'Kategori tidak ditemukan',
            ], Response::HTTP_NOT_FOUND);
    }

    public function rtm()
    {
        return $this->fractal($this->statistik->getStatistik($this->rtm->listStatistik()), new StatistikTransformer(), 'statistik-rtm')->respond();
    }

    public function bantuan()
    {
        return $this->fractal($this->statistik->getStatistik($this->bantuan->listStatistik()), new StatistikTransformer(), 'statistik-bantuan')->respond();
    }
}
