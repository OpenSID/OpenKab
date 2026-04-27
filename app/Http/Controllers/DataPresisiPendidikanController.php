<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DataPresisiPendidikanController extends Controller
{
    /**
     * Show the data presisi pendidikan index page.
     *
     * @return \Illuminate\View\View
     */
    public function index(): View
    {
        $title = 'Data Presisi Pendidikan';

        return view('data_pokok.data_presisi.pendidikan.index', compact('title'));
    }

    /**
     * Display detail data presisi pendidikan.
     *
     * @param Request $request HTTP request instance
     * @return View
     */
    public function detail(Request $request): View
    {
        $data = json_decode($request->data);

        return view('data_pokok.data_presisi.pendidikan.detail', ['data' => $data]);
    }

    /**
     * Display detail data presisi pendidikan with optional filter.
     *
     * @param Request $request HTTP request instance
     * @return View
     */
    public function detailData(Request $request): View
    {
        $colomn = '';
        $title = 'Data Presisi Pendidikan - '.$request->input('judul');
        $title = htmlspecialchars(strip_tags($title), ENT_QUOTES, 'UTF-8');
        $filter = $request->input('filter');

        if (isset($filter['tipe'], $filter['nilai']) && $filter['tipe'] && $filter['nilai']) {
            $colomn = $filter['tipe'].':'.$filter['nilai'];
        }

        return view('data_pokok.data_presisi.pendidikan.detail_data', compact('title', 'colomn'));
    }

    /**
     * Display print view for data presisi pendidikan.
     *
     * @param Request $request HTTP request instance
     * @return View
     */
    public function cetak(Request $request): View
    {
        return view('data_pokok.data_presisi.pendidikan.cetak', ['filter' => $request->getQueryString()]);
    }
}
