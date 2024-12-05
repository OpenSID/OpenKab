<?php

namespace App\Http\Controllers;

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
}
