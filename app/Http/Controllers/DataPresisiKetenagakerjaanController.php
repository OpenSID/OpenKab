<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetailDataPresisiKetenagakerjaanRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DataPresisiKetenagakerjaanController extends Controller
{
    public function index()
    {
        $title = 'Data Presisi Ketenagakerjaan';

        return view('data_pokok.data_presisi.ketenagakerjaan.index', compact('title'));
    }

    public function detail(Request $request)
    {
        $data = json_decode($request->data);

        return view('data_pokok.data_presisi.ketenagakerjaan.detail', ['data' => $data]);
    }

    public function cetak(Request $request)
    {
        return view('data_pokok.data_presisi.ketenagakerjaan.cetak', ['filter' => $request->getQueryString()]);
    }

     /**
     * Display detail data presisi ketenagakerjaan with optional filter.
     *
     * @param DetailDataPresisiKetenagakerjaanRequest $request HTTP request instance
     * @return View
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
