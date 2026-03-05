<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataPokokController extends Controller
{
    public function agama()
    {
        $title = 'Data Agama';

        return view('data_pokok.agama.index', ['title' => $title]);
    }

    public function detail_agama(Request $request)
    {
        $data = json_decode($request->data);

        return view('data_pokok.agama.detail', ['data' => $data]);
    }

    public function cetak_agama(Request $request)
    {
        return view('data_pokok.agama.cetak', ['filter' => $request->getQueryString()]);
    }

    public function kesehatan()
    {
        $title = 'Data Kependudukan dan Statistik';

        return view('data_pokok.kesehatan.index', ['title' => $title]);
    }

    public function cetakKesehatan(Request $request)
    {
        return view('data_pokok.kesehatan.cetak', ['filter' => $request->getQueryString()]);
    }

    public function jaminanSosial()
    {
        $title = 'Data Kepesertaan Program dan Statistik';

        return view('data_pokok.jaminan_sosial.index', ['title' => $title]);
    }

    public function detailJaminanSosial(Request $request)
    {
        $data = json_decode($request->data);

        return view('data_pokok.jaminan_sosial.detail', ['data' => $data]);
    }

    public function cetakJaminanSosial(Request $request)
    {
        return view('data_pokok.jaminan_sosial.cetak', ['filter' => $request->getQueryString()]);
    }

    public function pariwisata()
    {
        $title = 'Data Potensi Wisata dan Sumber Daya';

        return view('data_pokok.pariwisata.index', ['title' => $title]);
    }

    public function cetakPariwisata(Request $request)
    {
        return view('data_pokok.pariwisata.cetak', ['filter' => $request->getQueryString()]);
    }

    public function pendidikan()
    {
        return view('data_pokok.pendidikan.index');
    }

    public function cetakPendidikan(Request $request)
    {
        return view('data_pokok.pendidikan.cetak', ['filter' => $request->getQueryString()]);
    }

    public function ketenagakerjaan()
    {
        $title = 'Data Pekerjaan dan Pelatihan';

        return view('data_pokok.ketenagakerjaan.index', ['title' => $title]);
    }

    public function cetakKetenagakerjaan(Request $request)
    {
        return view('data_pokok.ketenagakerjaan.cetak', ['filter' => $request->getQueryString()]);
    }

    public function infrastruktur()
    {
        $title = 'Data Prasarana dan Sarana';

        return view('data_pokok.infrastruktur.index', ['title' => $title]);
    }

    public function cetakInfrastruktur(Request $request)
    {
        return view('data_pokok.infrastruktur.cetak', ['filter' => $request->getQueryString()]);
    }

    public function sandang()
    {
        $title = 'Data Sandang';

        return view('dtks.sandang.index', ['title' => $title]);
    }

    public function detail_sandang(Request $request)
    {
        $data = json_decode($request->data);

        return view('dtks.sandang.detail', ['data' => $data]);
    }

    public function cetak_sandang(Request $request)
    {
        return view('dtks.sandang.cetak', ['filter' => $request->getQueryString()]);
    }
}
