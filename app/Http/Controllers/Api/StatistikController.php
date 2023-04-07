<?php

namespace App\Http\Controllers\Api;

use App\Models\Rtm;
use App\Models\Umur;
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
        $data = match (request()->input('filter')['slug']) {
            'umur-rentang' => [
                'header' => Umur::countStatistik()->status()->orderBy('id')->get()->toArray(),
                'footer' => $this->contohFooter(),
            ],
            'umur-kategori' => [
                'header' => Umur::countStatistik()->status(0)->orderBy('id')->get()->toArray(),
                'footer' => $this->contohFooter(),
            ],
            default => null
        };

        return $this->fractal($this->statistik->getStatistik($data['header'], $data['footer']), new StatistikTransformer(), 'grafik')->respond();
    }

    public function rtm()
    {
        return $this->fractal($this->statistik->getStatistik([], $this->rtm->listStatistik()), new StatistikTransformer(), 'grafik')->respond();
    }

    public function bantuan()
    {
        return $this->fractal($this->statistik->getStatistik([], $this->bantuan->listStatistik()), new StatistikTransformer(), 'grafik')->respond();
    }

    // Hanya digunakan untuk test header
    private function contohFooter()
    {
        return [
            [
                "nama" => "Peserta",
                "jumlah" => 4,
                "laki_laki" => 2,
                "perempuan" => 2
            ],
            [
                "nama" => "Bukan Peserta",
                "jumlah" => 0,
                "laki_laki" => 0,
                "perempuan" => 0
            ],
            [
                "nama" => "Total",
                "jumlah" => 97,
                "laki_laki" => 46,
                "perempuan" => 51
            ]
        ];
    }
}
