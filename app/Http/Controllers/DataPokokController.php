<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataPokokController extends Controller
{
    public function agama_adat()
    {
        $title = 'Data Agama, Adat, Tradisi & Seni Budaya';

        return view('data_pokok.agama_adat.index', ['title' => $title]);
    }

    public function kesehatan()
    {
        $title = 'Data Kependudukan dan Statistik';

        return view('data_pokok.kesehatan.index', ['title' => $title]);
    }

    public function jaminanSosial()
    {
        $title = 'Data Kepesertaan Program dan Statistik';

        return view('data_pokok.jaminan_sosial.index', ['title' => $title]);
    }

    public function pariwisata()
    {
        $title = 'Data Potensi Wita dan Sumber Daya';

        return view('data_pokok.pariwisata.index', ['title' => $title]);
    }

    public function pendidikan()
    {
        return view('data_pokok.pendidikan.index');
    }

    public function ketenagakerjaan()
    {
        $title = 'Data Pekerjaan dan Pelatihan';

        return view('data_pokok.ketenagakerjaan.index', ['title' => $title]);
    }

    public function infrastruktur()
    {
        $title = 'Data Prasarana dan Sarana';

        return view('data_pokok.infrastruktur.index', ['title' => $title]);
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
