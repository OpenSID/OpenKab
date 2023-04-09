<?php

namespace App\Http\Controllers;

use App\Models\Umur;
use App\Models\Bantuan;
use App\Models\Keluarga;
use App\Models\Penduduk;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Repository\PendudukRepository;

class StatistikController extends Controller
{
    public function penduduk(PendudukRepository $pendudukRepository)
    {
        // $where = 'tweb_penduduk.sex = 2';
        // $result1 = $pendudukRepository->countStatistikByKategori('ref_penduduk_hamil', 'hamil', $where);
        // $result2 = Umur::countStatistikAkta()->status()->orderBy('id')->get();


        // return response()->json([
        //     'result1' => $result1,
        //     'result2' => $result2,
        // ]);

        // dd($result);
        return view('statistik.index', [
            'judul' => 'Penduduk',
            'kategori' => 'penduduk',
        ]);
    }

    public function keluarga()
    {
        return view('statistik.index', [
            'judul' => 'Keluarga',
            'kategori' => 'keluarga',
        ]);
    }

    public function rtm()
    {
        return view('statistik.index', [
            'judul' => 'RTM',
            'kategori' => 'rtm',
        ]);
    }

    public function cetak_rtm()
    {
        return view('statistik.rtm.cetak');
    }

    public function bantuan()
    {
        return view('statistik.index', [
            'judul' => 'Bantuan',
            'kategori' => 'bantuan',
        ]);
    }

    public function cetak($kategori, $id)
    {
        return view('statistik.cetak', [
            'kategori' => $kategori,
            'id' => $id,
        ]);
    }

}
