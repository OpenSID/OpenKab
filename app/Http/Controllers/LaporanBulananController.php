<?php

namespace App\Http\Controllers;

use App\Services\ConfigApiService;
use App\Services\StatistikPendudukApiService;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Session;

class LaporanBulananController extends Controller
{
    public $penduduk;

    public $config;

    public function __construct(StatistikPendudukApiService $penduduk, ConfigApiService $config)
    {
        $this->penduduk = $penduduk;
        $this->config = $config;

        Session::put('bulanku', date('n'));
        Session::put('tahunku', date('Y'));
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $data['page_title'] = 'Laporan Kependudukan Bulanan';
        $data['page_description'] = 'Laporan Kependudukan Bulanan';

        if (Session::has('bulanku')) {
            $data['bulanku'] = Session::get('bulanku');
        } else {
            $data['bulanku'] = date('n');
            Session::put('bulanku', $data['bulanku']);
        }

        if (Session::get('tahunku')) {
            $data['tahunku'] = Session::get('tahunku');
        } else {
            $data['tahunku'] = date('Y');
            Session::put('tahunku', $data['tahunku']);
        }

        $data['bulan'] = $data['bulanku'];
        $data['tahun'] = $data['tahunku'];

        $data['data_lengkap'] = true;
        $data['sesudah_data_lengkap'] = true;
        $tanggal_lengkap = $this->penduduk->logPenduduk();
        $dataLengkap = true;

        if (! empty($tanggal_lengkap) && strtotime($tanggal_lengkap)) {
            $tahun_bulan = (new DateTime($tanggal_lengkap))->format('Y-m');
        } else {
            $tahun_bulan = date('Y-m');
        }

        if (! $dataLengkap) {
            $data['data_lengkap'] = false;

            return view('laporan-bulanan.index', $data);
        }

        if ($tahun_bulan > $data['tahun'].'-'.$data['bulan']) {
            $data['sesudah_data_lengkap'] = false;

            return view('laporan-bulanan.index', $data);
        }

        $data['tgl_lengkap'] = $tanggal_lengkap;
        // $data['tahun_lengkap']      = (new DateTime($tanggal_lengkap))->format('Y');
        if (! empty($tanggal_lengkap) && is_string($tanggal_lengkap) && strtotime($tanggal_lengkap)) {
            $data['tahun_lengkap'] = (new DateTime($tanggal_lengkap))->format('Y');
        } else {
            $data['tahun_lengkap'] = date('Y');
        }

        $dataPenduduk = $this->penduduk->laporanBulanan($data['tahun'], $data['bulan']);

        if (auth()->user()->hasRole('administrator')) {
            $data['kabupatens'] = $this->config->kabupaten();

            $sesi = session()->has('kode_kabupaten') ? session('kode_kabupaten') : auth()->user()->kode_kabupaten;
            Session::put('kode_kabupaten', $sesi);
            $data['kode_kabupaten'] = session('kode_kabupaten');

            if (session()->has('kode_kabupaten')) {
                $data['kecamatans'] = $this->config->kecamatan([
                    'filter[kode_kabupaten]' => session('kode_kabupaten'),
                ]);
            } else {
                $data['kecamatans'] = [];
            }

            if (session()->has('kode_kecamatan')) {
                $data['desas'] = $this->config->desa([
                    'filter[kode_kecamatan]' => session('kode_kecamatan'),
                ]);
            } else {
                $data['desas'] = [];
            }
        } else {
            $data['kabupatens'] = $this->config->kabupaten([
                'filter[id]' => auth()->user()->kode_kabupaten,
            ]);

            if (session()->has('kode_kabupaten')) {
                $data['kecamatans'] = $this->config->kecamatan([
                    'filter[kode_kabupaten]' => session('kode_kabupaten'),
                ]);
            } else {
                $data['kecamatans'] = [];
            }

            if (session()->has('kode_kecamatan')) {
                $data['desas'] = $this->config->desa([
                    'filter[kode_kecamatan]' => session('kode_kecamatan'),
                ]);
            } else {
                $data['desas'] = [];
            }
        }

        if (collect($dataPenduduk)->isEmpty()) {
            $data['dataPenduduk'] = false;

            return view('laporan-bulanan.index', $data);
        } else {
            $data['dataPenduduk'] = true;

            return view('laporan-bulanan.index', array_merge($data, $dataPenduduk));
        }
    }

    public function filter(Request $request)
    {
        $bulanku = $request->bulan;
        if ($bulanku != '') {
            Session::put('bulanku', $bulanku);
        }

        $tahunku = $request->tahun;
        if ($tahunku != '') {
            Session::put('tahunku', $tahunku);
        }

        $kabupaten = $request->kabupaten;
        $kecamatan = $request->kecamatan;
        $desa = $request->desa;
        if ($kabupaten) {
            Session::put('kode_kabupaten', $kabupaten);
            Session::forget('kode_kecamatan');
            Session::forget('kode_desa');
        } else {
            Session::forget('kode_kabupaten');
            Session::forget('kode_kecamatan');
            Session::forget('kode_desa');
            $kecamatan = $desa = '';
        }

        if ($kecamatan) {
            Session::put('kode_kecamatan', $kecamatan);
            Session::forget('kode_desa');
        } else {
            Session::forget('kode_kecamatan');
            Session::forget('kode_desa');
            $desa = '';
        }
        if ($desa != '') {
            Session::put('kode_desa', $desa);
            Session::put('kode_kecamatan', $kecamatan);
            Session::put('kode_kabupaten', $kabupaten);
        } else {
            Session::forget('kode_desa');
        }

        return redirect()->route('laporan-bulanan.index');
    }

    public function detailPenduduk($rincian, $tipe)
    {
        $data['rincian'] = $rincian;
        $data['tipe'] = $tipe;
        $data['tahun'] = session('tahunku');
        $data['bulan'] = session('bulanku');
        $data['kode_desa'] = session('kode_desa');
        $data['kode_kabupaten'] = session('kode_kabupaten');
        $data['kode_kecamatan'] = session('kode_kecamatan');
        $data['page_title'] = 'Laporan Kependudukan Bulanan';
        $data['page_description'] = 'Rincian Kependudukan Bulanan';

        $data['title'] = $this->generateTitle($rincian, $tipe);

        return view('laporan-bulanan.detail.index', $data);
    }

    private function generateTitle($rincian, $tipe)
    {
        $titlePeriode = strtoupper(bulan(session('bulanku'))).' '.session('tahunku');
        $keluarga = ['kk', 'kk_l', 'kk_p'];
        switch (strtolower($rincian)) {
            case 'awal':
                $title = 'PENDUDUK/KELUARGA AWAL BULAN '.$titlePeriode;
                break;

            case 'lahir':
                $title = (in_array($tipe, $keluarga) ? 'KELUARGA BARU BULAN ' : 'KELAHIRAN BULAN ').$titlePeriode;
                break;

            case 'mati':
                $title = 'KEMATIAN BULAN '.$titlePeriode;
                break;

            case 'datang':
                $title = 'PENDATANG BULAN '.$titlePeriode;
                break;

            case 'pindah':

                $title = 'PINDAH/KELUAR PERGI BULAN '.$titlePeriode;
                break;

            case 'hilang':
                $title = 'PENDUDUK HILANG BULAN '.$titlePeriode;
                break;

            case 'akhir':
                $title = 'PENDUDUK/KELUARGA AKHIR BULAN '.$titlePeriode;
                break;
            default:
                $title = 'LAPORAN BULAN '.$titlePeriode;
                break;
        }

        return $title;
    }

    public function exportExcel()
    {
        $data['tahun'] = session('tahunku');
        $data['bulan'] = session('bulanku');
        $dataPenduduk = (array) $this->penduduk->laporanBulanan($data['tahun'], $data['bulan']);

        if (collect($dataPenduduk)->isEmpty()) {
            $data['dataPenduduk'] = false;
            $html = view('laporan-bulanan.cetak', $data)->render();
        } else {
            $data['dataPenduduk'] = true;
            $html = view('laporan-bulanan.cetak', array_merge($data, $dataPenduduk))->render();
        }

        return response($html)
        ->header('Content-Type', 'application/vnd.ms-excel')
        ->header('Content-Disposition', 'attachment; filename="laporan.xls"');
    }

    public function exportExcelDetail($rincian, $tipe)
    {
        $tahun = session('tahunku');
        $bulan = session('bulanku');
        $kode_kabupaten = session('kode_kabupaten');
        $kode_kecamatan = session('kode_kecamatan');
        $kode_desa = session('kode_desa');
        $filters = [
            'filter[rincian]' => $rincian,
            'filter[tipe]' => $tipe,
            'filter[tahun]' => $tahun,
            'filter[bulan]' => $bulan,
            'filter[kode_kabupaten]' => $kode_kabupaten,
            'filter[kode_kecamatan]' => $kode_kecamatan,
            'filter[kode_desa]' => $kode_desa,
            'kode_kabupaten' => $kode_kabupaten,
            'kode_kecamatan' => $kode_kecamatan,
            'kode_desa' => $kode_desa,
            'page[size]' => 10000000,
        ];
        $data = ['main' => $this->penduduk->sumberData($filters),
            'title' => $this->generateTitle($rincian, $tipe)];

        $data['page_title'] = 'Laporan Kependudukan Bulanan';
        $data['page_description'] = 'Rincian Kependudukan Bulanan';

        $html = view('laporan-bulanan.cetak_detail', $data)->render();

        return response($html)
        ->header('Content-Type', 'application/vnd.ms-excel')
        ->header('Content-Disposition', 'attachment; filename="laporan.xls"');
    }

    public function getSesi()
    {
        if (auth()->user()->hasRole('administrator')) {
            $data['kabupatens'] = $this->config->kabupaten();

            $sesi = session()->has('kabupaten.kode_kabupaten') ? session('kabupaten.kode_kabupaten') : auth()->user()->kode_kabupaten;
            Session::put('kabupaten.kode_kabupaten', $sesi);
            $data['kode_kabupaten'] = session('kabupaten.kode_kabupaten');

            if (session()->has('kabupaten.kode_kabupaten')) {
                $data['kecamatans'] = $this->config->kecamatan([
                    'filter[kode_kabupaten]' => session('kabupaten.kode_kabupaten'),
                ]);
            } else {
                $data['kecamatans'] = [];
            }

            if (session()->has('kecamatan.kode_kecamatan')) {
                $data['desas'] = $this->config->desa([
                    'filter[kode_kecamatan]' => session('kecamatan.kode_kecamatan'),
                ]);
            } else {
                $data['desas'] = [];
            }
        } else {
            $data['kabupatens'] = $this->config->kabupaten([
                'filter[id]' => auth()->user()->kode_kabupaten,
            ]);

            if (session()->has('kabupaten.kode_kabupaten')) {
                $data['kecamatans'] = $this->config->kecamatan([
                    'filter[kode_kabupaten]' => session('kabupaten.kode_kabupaten'),
                ]);
            } else {
                $data['kecamatans'] = [];
            }

            if (session()->has('kecamatan.kode_kecamatan')) {
                $data['desas'] = $this->config->desa([
                    'filter[kode_kecamatan]' => session('kecamatan.kode_kecamatan'),
                ]);
            } else {
                $data['desas'] = [];
            }
        }

        return $data;
    }
}
