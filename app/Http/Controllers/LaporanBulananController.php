<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Services\StatistikPendudukApiService;
use Illuminate\Support\Facades\Session;

class LaporanBulananController extends Controller
{
    public $penduduk;

    public function __construct(StatistikPendudukApiService $penduduk){

        $this->penduduk = $penduduk;

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
            $data['bulanku']        = date('n');
            Session::put('bulanku', $data['bulanku']);
        }

        if (Session::get('tahunku')) {
            $data['tahunku'] = Session::get('tahunku');
        } else {
            $data['tahunku']        = date('Y');
            Session::put('tahunku', $data['tahunku']);
        }

        $data['bulan']                = $data['bulanku'];
        $data['tahun']                = $data['tahunku'];

        $data['data_lengkap'] = true;
        $data['sesudah_data_lengkap'] = true;
        $tanggal_lengkap = $this->penduduk->logPenduduk();
        $dataLengkap = true;

        $tahun_bulan = (new DateTime($tanggal_lengkap))->format('Y-m');

        if (! $dataLengkap) {
            $data['data_lengkap'] = false;
            return view('laporan-bulanan.index', $data);
        }

        $tahun_bulan = (new DateTime($tanggal_lengkap))->format('Y-m');
        if ($tahun_bulan > $data['tahun'] . '-' . $data['bulan']) {
            $data['sesudah_data_lengkap'] = false;
            return view('laporan-bulanan.index', $data);
        }

        $data['tgl_lengkap']        = $tanggal_lengkap;
        $data['tahun_lengkap']      = (new DateTime($tanggal_lengkap))->format('Y');
        $dataPenduduk               = $this->penduduk->laporanBulanan($data['tahun'], $data['bulan']);

        return view('laporan-bulanan.index', array_merge($data, $dataPenduduk));
    }

    public function bulan(Request $request)
    {
        $bulanku = $request->bulan;
        if ($bulanku != '') {
            Session::put('bulanku', $bulanku);
        } else {
            Session::forget('bulanku');
        }

        $tahunku = $request->tahun;
        if ($tahunku != '') {
            Session::put('tahunku', $tahunku);
        } else {
            Session::forget('tahunku');
        }
        
        return redirect()->route('laporan-bulanan.index');
    }

    public function detailPenduduk($rincian, $tipe)
    {
        $data            = $this->penduduk->sumberData($rincian, $tipe, session('tahunku'), session('bulanku'));

        $data['rincian'] = $rincian;
        $data['tipe']    = $tipe;

        $data['page_title'] = 'Laporan Kependudukan Bulanan';
        $data['page_description'] = 'Rincian Kependudukan Bulanan';

        return view('laporan-bulanan.detail.index', $data);
    }

    public function exportExcel()
    {
        $data['tahun'] = session('tahunku');
        $data['bulan'] = session('bulanku');
        $dataPenduduk = (array) $this->penduduk->laporanBulanan($data['tahun'], $data['bulan']);

        $html = view('laporan-bulanan.cetak', array_merge($data, $dataPenduduk))->render();

        return response($html)
        ->header('Content-Type', 'application/vnd.ms-excel')
        ->header('Content-Disposition', 'attachment; filename="laporan.xls"');
        
    }

}
