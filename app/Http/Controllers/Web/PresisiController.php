<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Anak;
use App\Models\IbuHamil;
use App\Models\Penduduk;
use App\Models\Keluarga;
use App\Models\Bantuan;
use App\Models\Posyandu;
use App\Models\SasaranPaud;
use App\Services\RekapService;
use App\Services\StuntingService;

class PresisiController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index()
    {
        $totalDesa = 0;
        $pendudukSummary = 0;
        $configSummary = 0;
        $keluargaSummary = 0;
        $categoriesItems = [
            ['key' => 'kecamatan', 'text' => 'kecamatan', 'value' => $configSummary, 'icon' => 'web/img/kecamatan.jpg'],
            ['key' => 'desa', 'text' => 'desa/kelurahan', 'value' => $totalDesa, 'icon' => 'web/img/kelurahan.jpg'],
            ['key' => 'penduduk', 'text' => 'jumlah penduduk', 'value' => $pendudukSummary, 'icon' => 'web/img/penduduk.jpg'],
            ['key' => 'keluarga', 'text' => 'jumlah keluarga', 'value' => $keluargaSummary, 'icon' => 'web/img/bantuan.jpg'],
        ];
        $listKecamatan = ['' => 'Pilih Kecamatan'];
        $listDesa = ['' => 'Pilih Desa'];

        return view('presisi.index', compact('categoriesItems', 'listKecamatan', 'listDesa'));
    }

    public function kependudukan()
    {
        $statistik = Penduduk::KATEGORI_STATISTIK;

        return view('presisi.kependudukan.index', compact('statistik'));
    }

    public function kesehatan($kuartal = null, $tahun = null, $id = null)
    {
        if ($kuartal < 1 || $kuartal > 4) {
            $kuartal = null;
        }

        if ($kuartal == null) {
            $bulanSekarang = date('m');
            if ($bulanSekarang <= 3) {
                $kuartal = 1;
            } elseif ($bulanSekarang <= 6) {
                $kuartal = 2;
            } elseif ($bulanSekarang <= 9) {
                $kuartal = 3;
            } elseif ($bulanSekarang <= 12) {
                $kuartal = 4;
            }
        }

        if ($tahun == null) {
            $tahun = date('Y');
        }

        $data = $this->sumber_data($kuartal, $tahun, $id);

        return view('presisi.kesehatan.index', compact('data'));
    }

    private function sumber_data($kuartal = null, $tahun = null, $id = null)
    {
        $rekap = new RekapService();

        if ($kuartal < 1 || $kuartal > 4) {
            $kuartal = null;
        }

        if ($kuartal == null) {
            $bulanSekarang = date('m');
            if ($bulanSekarang <= 3) {
                $kuartal = 1;
            } elseif ($bulanSekarang <= 6) {
                $kuartal = 2;
            } elseif ($bulanSekarang <= 9) {
                $kuartal = 3;
            } elseif ($bulanSekarang <= 12) {
                $kuartal = 4;
            }
        }

        if ($tahun == null) {
            $tahun = date('Y');
        }

        if ($kuartal == 1) {
            $batasBulanBawah = 1;
            $batasBulanAtas = 3;
        } elseif ($kuartal == 2) {
            $batasBulanBawah = 4;
            $batasBulanAtas = 6;
        } elseif ($kuartal == 3) {
            $batasBulanBawah = 7;
            $batasBulanAtas = 9;
        } elseif ($kuartal == 4) {
            $batasBulanBawah = 10;
            $batasBulanAtas = 12;
        } else {
            exit('Terjadi Kesalahan di kuartal!');
        }

        $JTRT_IbuHamil = IbuHamil::query()
            ->distinct()
            ->join('kia', 'ibu_hamil.kia_id', '=', 'kia.id')
            ->whereMonth('ibu_hamil.created_at', '>=', $batasBulanBawah)
            ->whereMonth('ibu_hamil.created_at', '<=', $batasBulanAtas)
            ->whereYear('ibu_hamil.created_at', $tahun)
            ->selectRaw('ibu_hamil.kia_id as kia_id')
            ->get();

        $JTRT_BulananAnak = Anak::query()
            ->distinct()
            ->join('kia', 'bulanan_anak.kia_id', '=', 'kia.id')
            ->whereMonth('bulanan_anak.created_at', '>=', $batasBulanBawah)
            ->whereMonth('bulanan_anak.created_at', '<=', $batasBulanAtas)
            ->whereYear('bulanan_anak.created_at', $tahun)
            ->selectRaw('bulanan_anak.kia_id as kia_id')
            ->get();

        foreach ($JTRT_IbuHamil as $item_ibuHamil) {
            $dataNoKia[] = $item_ibuHamil;

            foreach ($JTRT_BulananAnak as $item_bulananAnak) {
                if (! in_array($item_bulananAnak, $dataNoKia)) {
                    $dataNoKia[] = $item_bulananAnak;
                }
            }
        }

        $ibu_hamil = $rekap->get_data_ibu_hamil($kuartal, $tahun, $id);
        $bulanan_anak = $rekap->get_data_bulanan_anak($kuartal, $tahun, $id);

        //HITUNG KEK ATAU RISTI
        $jumlahKekRisti = 0;

        foreach ($ibu_hamil['dataFilter'] ?? [] as $item) {
            if (! in_array($item['user']['status_kehamilan'], [null, '1'])) {
                $jumlahKekRisti++;
            }
        }

        //HITUNG HASIL PENGUKURAN TIKAR PERTUMBUHAN
        $status_tikar = collect(Anak::STATUS_TIKAR_ANAK)->pluck('simbol', 'id');
        $tikar = ['TD' => 0, 'M' => 0, 'K' => 0, 'H' => 0];

        if ($bulanan_anak['dataGrup'] != null) {
            foreach ($bulanan_anak['dataGrup'] as $detail) {
                $totalItem = count($detail);
                $i = 0;

                foreach ($detail as $item) {
                    if (++$i === $totalItem) {
                        $tikar[$status_tikar[$item['status_tikar']]]++;
                    }
                }
            }

            $jumlahGiziBukanNormal = 0;

            foreach ($bulanan_anak['dataFilter'] as $item) {
                // N = 1
                if ($item['umur_dan_gizi']['status_gizi'] != 'N') {
                    $jumlahGiziBukanNormal++;
                }
            }
        } else {
            $dataNoKia = [];
            $jumlahGiziBukanNormal = 0;
        }

        //START ANAK PAUD------------------------------------------------------------
        $totalAnak = [
            'januari' => ['total' => 0, 'v' => 0],
            'februari' => ['total' => 0, 'v' => 0],
            'maret' => ['total' => 0, 'v' => 0],
            'april' => ['total' => 0, 'v' => 0],
            'mei' => ['total' => 0, 'v' => 0],
            'juni' => ['total' => 0, 'v' => 0],
            'juli' => ['total' => 0, 'v' => 0],
            'agustus' => ['total' => 0, 'v' => 0],
            'september' => ['total' => 0, 'v' => 0],
            'oktober' => ['total' => 0, 'v' => 0],
            'november' => ['total' => 0, 'v' => 0],
            'desember' => ['total' => 0, 'v' => 0],
        ];

        $anak2sd6 = SasaranPaud::query();
        $anak2sd6->whereYear('sasaran_paud.created_at', $tahun)->get();

        foreach ($anak2sd6 as $datax) {
            if ($datax->januari != 'belum') {
                $totalAnak['januari']['total']++;
            }
            if ($datax->februari != 'belum') {
                $totalAnak['februari']['total']++;
            }
            if ($datax->maret != 'belum') {
                $totalAnak['maret']['total']++;
            }
            if ($datax->april != 'belum') {
                $totalAnak['april']['total']++;
            }
            if ($datax->mei != 'belum') {
                $totalAnak['mei']['total']++;
            }
            if ($datax->juni != 'belum') {
                $totalAnak['juni']['total']++;
            }
            if ($datax->juli != 'belum') {
                $totalAnak['juni']['total']++;
            }
            if ($datax->agustus != 'belum') {
                $totalAnak['agustus']['total']++;
            }
            if ($datax->september != 'belum') {
                $totalAnak['juni']['total']++;
            }
            if ($datax->oktober != 'belum') {
                $totalAnak['oktober']['total']++;
            }
            if ($datax->november != 'belum') {
                $totalAnak['november']['total']++;
            }
            if ($datax->desember != 'belum') {
                $totalAnak['desember']['total']++;
            }

            if ($datax->januari == 'v') {
                $totalAnak['januari']['v']++;
            }
            if ($datax->februari == 'v') {
                $totalAnak['februari']['v']++;
            }
            if ($datax->maret == 'v') {
                $totalAnak['maret']['v']++;
            }
            if ($datax->april == 'v') {
                $totalAnak['april']['v']++;
            }
            if ($datax->mei == 'v') {
                $totalAnak['mei']['v']++;
            }
            if ($datax->juni == 'v') {
                $totalAnak['juni']['v']++;
            }
            if ($datax->juli == 'v') {
                $totalAnak['juni']['v']++;
            }
            if ($datax->agustus == 'v') {
                $totalAnak['agustus']['v']++;
            }
            if ($datax->september == 'v') {
                $totalAnak['juni']['v']++;
            }
            if ($datax->oktober == 'v') {
                $totalAnak['oktober']['v']++;
            }
            if ($datax->november == 'v') {
                $totalAnak['november']['v']++;
            }
            if ($datax->desember == 'v') {
                $totalAnak['desember']['v']++;
            }
        }

        $dataAnak0sd2Tahun = ['jumlah' => 0, 'persen' => 0];
        if ($kuartal == 1) {
            $jmlAnk = $totalAnak['januari']['total'] + $totalAnak['februari']['total'] + $totalAnak['maret']['total'];
            $jmlV = $totalAnak['januari']['v'] + $totalAnak['februari']['v'] + $totalAnak['maret']['v'];
        } elseif ($kuartal == 2) {
            $jmlAnk = $totalAnak['april']['total'] + $totalAnak['mei']['total'] + $totalAnak['juni']['total'];
            $jmlV = $totalAnak['april']['v'] + $totalAnak['mei']['v'] + $totalAnak['juni']['v'];
        } elseif ($kuartal == 3) {
            $jmlAnk = $totalAnak['agustus']['total'];
            $jmlV = $totalAnak['agustus']['v'];
        } elseif ($kuartal == 4) {
            $jmlAnk = $totalAnak['oktober']['total'] + $totalAnak['november']['total'] + $totalAnak['desember']['total'];
            $jmlV = $totalAnak['oktober']['v'] + $totalAnak['november']['v'] + $totalAnak['desember']['v'];
        }
        $dataAnak0sd2Tahun['jumlah'] = $jmlV;
        $dataAnak0sd2Tahun['persen'] = $jmlAnk !== 0 ? number_format($jmlV / $jmlAnk * 100, 2) : 0;

        //END ANAK PAUD------------------------------------------------------------

        $data = $this->widget();
        $data['navigasi'] = 'scorcard-konvergensi';
        $data['dataAnak0sd2Tahun'] = $dataAnak0sd2Tahun;
        $data['id'] = $id;
        $data['posyandu'] = Posyandu::get();
        $data['JTRT'] = count($dataNoKia);
        $data['jumlahKekRisti'] = $jumlahKekRisti;
        $data['jumlahGiziBukanNormal'] = $jumlahGiziBukanNormal;
        $data['tikar'] = $tikar;
        $data['ibu_hamil'] = $ibu_hamil;
        $data['bulanan_anak'] = $bulanan_anak;
        $data['dataTahun'] = $data['ibu_hamil']['dataTahun'];
        $data['kuartal'] = $kuartal;
        $data['_tahun'] = $tahun;
        $data['aktif'] = 'scorcard';
        $stunting = new StuntingService(['idPosyandu' => $id, 'kuartal' => $kuartal, 'tahun' => $tahun]);
        $data['chartStuntingUmurData'] = $stunting->chartStuntingUmurData();
        $data['chartStuntingPosyanduData'] = $stunting->chartPosyanduData();

        return $data;
    }

    private function widget(): array
    {
        return [
            'bulanIniIbuHamil' => IbuHamil::whereMonth('created_at', date('m'))->count(),
            'bulanIniAnak' => Anak::whereMonth('created_at', date('m'))->count(),
            'totalIbuHamil' => IbuHamil::count(),
            'totalAnak' => Anak::count(),
            'totalAnakNormal' => Anak::where('status_gizi', 1)->count(),
            'totalAnakResiko' => Anak::whereIn('status_gizi', [2, 3])->count(),
            'totalAnakStunting' => Anak::where('status_gizi', 4)->count(),
            'widgets' => [
                [
                    'title' => 'Ibu Hamil Periksa Bulan ini',
                    'icon' => 'ion-woman',
                    'bg-color' => 'bg-blue',
                    'bg-icon' => 'ion-stats-bars',
                    'total' => IbuHamil::whereMonth('created_at', date('m'))->count(),
                ],
                [
                    'title' => 'Anak Periksa Bulan ini',
                    'icon' => 'ion-woman',
                    'bg-color' => 'bg-gray',
                    'bg-icon' => 'ion-stats-bars',
                    'total' => Anak::whereMonth('created_at', date('m'))->count(),
                ],
                [
                    'title' => 'Ibu Hamil & Anak 0-23 Bulan',
                    'icon' => 'ion-woman',
                    'bg-color' => 'bg-green',
                    'bg-icon' => 'ion-stats-bars',
                    'total' => IbuHamil::count() + Anak::count(),
                ],
                [
                    'title' => 'Anak 0-23 Bulan Normal',
                    'icon' => 'ion-woman',
                    'bg-color' => 'bg-green',
                    'bg-icon' => 'ion-stats-bars',
                    'total' => Anak::normal()->count(),
                ],
                [
                    'title' => 'Anak 0-23 Bulan Resiko Stunting',
                    'icon' => 'ion-woman',
                    'bg-color' => 'bg-yellow',
                    'bg-icon' => 'ion-stats-bars',
                    'total' => Anak::resikoStunting()->count(),
                ],
                [
                    'title' => 'Anak 0-23 Bulan Stunting',
                    'icon' => 'ion-woman',
                    'bg-color' => 'bg-red',
                    'bg-icon' => 'ion-stats-bars',
                    'total' => Anak::stunting()->count(),
                ],
            ],
        ];
    }

    public function keluarga($id = "")
    {
        $statistik = Keluarga::KATEGORI_STATISTIK;
        $totalDesa = 0;
        $pendudukSummary = 0;
        $configSummary = 0;
        $keluargaSummary = 0;
        $categoriesItems = [
            ['key' => 'kecamatan', 'text' => 'kecamatan', 'value' => $configSummary, 'icon' => 'web/img/kecamatan.jpg'],
            ['key' => 'desa', 'text' => 'desa/kelurahan', 'value' => $totalDesa, 'icon' => 'web/img/kelurahan.jpg'],
            ['key' => 'penduduk', 'text' => 'jumlah penduduk', 'value' => $pendudukSummary, 'icon' => 'web/img/penduduk.jpg'],
            ['key' => 'keluarga', 'text' => 'jumlah keluarga', 'value' => $keluargaSummary, 'icon' => 'web/img/bantuan.jpg'],
        ];

        return view('presisi.keluarga.index', compact('statistik', 'id', 'categoriesItems'));
    }
      
    public function bantuan($id = "")
    {
        $totalDesa = 0;
        $pendudukSummary = 0;
        $configSummary = 0;
        $keluargaSummary = 0;
        $categoriesItems = [
            ['key' => 'kecamatan', 'text' => 'kecamatan', 'value' => $configSummary, 'icon' => 'web/img/kecamatan.jpg'],
            ['key' => 'desa', 'text' => 'desa/kelurahan', 'value' => $totalDesa, 'icon' => 'web/img/kelurahan.jpg'],
            ['key' => 'penduduk', 'text' => 'jumlah penduduk', 'value' => $pendudukSummary, 'icon' => 'web/img/penduduk.jpg'],
            ['key' => 'keluarga', 'text' => 'jumlah keluarga', 'value' => $keluargaSummary, 'icon' => 'web/img/bantuan.jpg'],
        ];
        return view('presisi.bantuan.index', compact('id', 'categoriesItems'));
    }
}
