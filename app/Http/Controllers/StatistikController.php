<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class StatistikController extends Controller
{
    public function index()
    {
        // return view('statistik.bantuan');
    }

    public function bantuan()
    {
        return view('statistik.bantuan.index');
    }

    public function cetak_bantuan($tanggal = null)
    {
        return view('statistik.bantuan.cetak');
    }

    public function statistik_penduduk()
    {
        $data = array(
            ["id" => "umur_rentang", "nama" => "Umur (Rentang)"],
            ["id" => "umur_kategori", "nama" => "Umur (Kategori)"],
            ["id" => "pend_kk", "nama" => "Pendidikan Dalam KK"],
            ["id" => "pend_tempuh", "nama" => "Pendidikan Sedang Ditempuh"],
            ["id" => "kerja", "nama" => "Pekerjaan"],
            ["id" => "kawin", "nama" => "Status Perkawinan"],
            ["id" => "agama", "nama" => "Agama"],
            ["id" => "jk", "nama" => "Jenis Kelamin"],
            ["id" => "hub_kk", "nama" => "Hubungan Dalam KK"],
            ["id" => "negara", "nama" => "Warga Negara"],
            ["id" => "status", "nama" => "Status Penduduk"],
            ["id" => "darah", "nama" => "Golongan Darah"],
            ["id" => "cacat", "nama" => "Penyandang Cacat"],
            ["id" => "sakit", "nama" => "Penyakit Menahun"],
            ["id" => "kb", "nama" => "Aseptor KB"],
            ["id" => "akta", "nama" => "Akta Kelahiran"],
            ["id" => "ktp", "nama" => "Kepemilikan KTP"],
            ["id" => "asuransi", "nama" => "Asuransi Kesehatan"],
            ["id" => "covid", "nama" => "Status Covid"],
            ["id" => "suku", "nama" => "Suku / Etnis"],
            ["id" => "bpjs_kerja", "nama" => "BPJS Ketenagakerjaan"],
            ["id" => "hamil", "nama" => "Status Kehamilan"]
        );
        return view('statistik.statistik_penduduk.index', ['data' => $data]);
    }

    public function cetak_statistik_penduduk($tanggal = null)
    {
        return view('statistik.statistik_penduduk.cetak');
    }
}
