<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetailDataPresisiSeniBudayaRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller untuk Data Presisi Seni Budaya
 *
 * Menangani operasi CRUD dan display untuk data seni budaya
 */
class DataPresisiSeniBudayaController extends Controller
{
    /**
     * Menampilkan halaman utama data seni budaya
     */
    public function index(): View
    {
        $title = 'Data Presisi Seni Budaya';

        return view('data_pokok.data_presisi.seni_budaya.index', compact('title'));
    }

    /**
     * Menampilkan detail data seni budaya berdasarkan data yang dipilih
     */
    public function detail(Request $request): View
    {
        $data = json_decode($request->data);

        return view('data_pokok.data_presisi.seni_budaya.detail', ['data' => $data]);
    }

    /**
     * Menampilkan halaman detail data statistik seni budaya
     * dengan filter berdasarkan kategori dan nilai
     */
    public function detailData(DetailDataPresisiSeniBudayaRequest $request): View
    {
        $title = 'Seni Budaya - '.($request->filled('judul') ? htmlspecialchars(strip_tags($request->input('judul'))) : '');

        $colomn = '';

        $filter = $request->input('filter');
        if (isset($filter['tipe'], $filter['nilai']) && $filter['tipe'] !== '' && $filter['nilai'] !== '') {
            if ($filter['tipe'] === 'jenis_seni_yang_dikuasai') {
                $filter['tipe'] = $filter['tipe'].'.sub_jenis_seni';
            }
            $colomn = $filter['tipe'].':'.$filter['nilai'];
        }

        return view('data_pokok.data_presisi.seni_budaya.detail_data', [
            'title' => $title,
            'colomn' => $colomn,
        ]);
    }

    /**
     * Menampilkan halaman cetak data seni budaya
     */
    public function cetak(Request $request): View
    {
        return view('data_pokok.data_presisi.seni_budaya.cetak', ['filter' => $request->getQueryString()]);
    }
}
