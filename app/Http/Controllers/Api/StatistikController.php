<?php

namespace App\Http\Controllers\Api;

use App\Models\Rtm;
use App\Models\Umur;
use App\Models\Hamil;
use App\Models\Penduduk;
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

    public function __construct(PendudukRepository $penduduk, RtmRepository $rtm, BantuanRepository $bantuan, StatistikRepository $statistik)
    {
        $this->penduduk = $penduduk;
        $this->rtm = $rtm;
        $this->bantuan = $bantuan;
        $this->statistik = $statistik;
    }

    public function penduduk()
    {
        return $this->fractal($this->statistik->getStatistik($this->penduduk->listStatistik()), new StatistikTransformer(), 'statistik-penduduk')->respond();
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
