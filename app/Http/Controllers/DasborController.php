<?php

namespace App\Http\Controllers;

use App\Models\Identitas;

class DasborController extends Controller
{
    public function index()
    {
        $identitas = Identitas::first();
        $data['nama_aplikasi'] = $identitas->nama_aplikasi ?? config('app.namaAplikasi');
        $data['sebutanKab'] = $identitas->sebutan_kab ?? config('app.sebutanKab');
        $nama_kabupaten = preg_replace("/KAB/", "", $identitas->nama_kabupaten) ?? config('app.namaKab');
        $data['nama_kabupaten'] = strtolower($data['sebutanKab']) == 'kota' ? $nama_kabupaten : $data['sebutanKab'] . ' ' . $nama_kabupaten;

        return view('dasbor.index', compact('data'));
    }
}
