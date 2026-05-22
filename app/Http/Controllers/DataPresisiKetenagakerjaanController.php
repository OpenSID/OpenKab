<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetailDataPresisiKetenagakerjaanRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller untuk mengelola data presisi ketenagakerjaan.
 *
 * Menangani tampilan index, detail, cetak, dan detail data
 * presisi ketenagakerjaan.
 */
class DataPresisiKetenagakerjaanController extends Controller
{
    /**
     * Menampilkan halaman index data presisi ketenagakerjaan.
     *
     * @return View halaman index data presisi ketenagakerjaan
     */
    public function index(): View
    {
        $title = 'Data Presisi Ketenagakerjaan';

        return view('data_pokok.data_presisi.ketenagakerjaan.index', compact('title'));
    }

    /**
     * Menampilkan halaman detail data presisi ketenagakerjaan.
     *
     * @param Request $request HTTP request yang berisi data JSON pada field `data`
     * @return View halaman detail data presisi ketenagakerjaan
     */
    public function detail(Request $request): View
    {
        $data = json_decode($request->data);

        if ($data === null) {
            $data = (object) [];
        }

        return view('data_pokok.data_presisi.ketenagakerjaan.detail', ['data' => $data]);
    }

    /**
     * Menampilkan halaman cetak data presisi ketenagakerjaan.
     *
     * @param Request $request HTTP request yang berisi parameter query string sebagai filter
     * @return View halaman cetak data presisi ketenagakerjaan
     */
    public function cetak(Request $request): View
    {
        return view('data_pokok.data_presisi.ketenagakerjaan.cetak', ['filter' => $request->getQueryString()]);
    }

    /**
     * Display detail data presisi ketenagakerjaan with optional filter.
     *
     * @param DetailDataPresisiKetenagakerjaanRequest $request HTTP request instance yang berisi filter dan judul
     * @return View halaman detail data presisi ketenagakerjaan dengan filter
     */
    public function detailData(DetailDataPresisiKetenagakerjaanRequest $request): View
    {
        $colomn = '';
        $title = 'Data Presisi Ketenagakerjaan - ' . $request->input('judul', '');
        $title = htmlspecialchars(strip_tags($title), ENT_QUOTES, 'UTF-8');
        $filter = $request->input('filter');

        if (isset($filter['tipe'], $filter['nilai']) && $filter['tipe'] && $filter['nilai']) {
            $colomn = $filter['tipe'] . ':' . $filter['nilai'];
        }

        return view('data_pokok.data_presisi.ketenagakerjaan.detail_data', compact('title', 'colomn'));
    }
}
