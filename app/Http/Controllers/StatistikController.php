<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StatistikController extends Controller
{
    public function penduduk()
    {
        return view('statistik.index', [
            'detailLink' => url('penduduk'),
            'judul' => 'Penduduk',
            'default_kategori' => request('item_selected', 'rentang-umur'),
        ]);
    }

    public function keluarga()
    {
        return view('statistik.index', [
            'detailLink' => url('keluarga'),
            'judul' => 'Keluarga',
            'default_kategori' => 'kelas-sosial',
        ]);
    }

    public function rtm()
    {
        return view('statistik.index', [
            'detailLink' => url('rtm'),
            'judul' => 'RTM',
            'default_kategori' => 'bdt',
        ]);
    }

    public function cetak_rtm()
    {
        return view('statistik.rtm.cetak');
    }

    public function bantuan()
    {
        return view('statistik.index', [
            'detailLink' => url('penduduk'),
            'judul' => 'Bantuan',
            'default_kategori' => 'penduduk',
            'filter_tahun' => true
        ]);
    }

    public function cetak($kategori, $id, Request $request)
    {
        $filter = array_filter($request->all());

        return view('statistik.cetak', [
            'kategori' => $kategori,
            'id' => $id,
            'filter' => $filter['filter'],
        ]);
    }

    public function detail($tipe = '0', $nomor = 0, $sex = null, $kategori = null, $kategori_id = null)
    {
        return view('statistik.detail', [
            'route_back' => "statistik.{$kategori}",
            'judul' => strtoupper($kategori),
            'default_kategori' => $kategori_id,
            'tipe' => $tipe,
            'nomor' => $nomor,
            'sex' => $sex,
        ]);
    }

    public function detailPenduduk($tipe = '0', $nomor = 0, $sex = null, $kategori = null, $kategori_id = null)
    {
        return view('statistik.detail-bantuan', [
            'route_back' => "statistik.{$kategori}",
            'judul' => strtoupper($kategori),
            'default_kategori' => $kategori_id,
            'tipe' => $tipe,
            'nomor' => $nomor,
            'sex' => $sex,
        ]);
    }
}
