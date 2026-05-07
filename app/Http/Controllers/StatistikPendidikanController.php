<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class StatistikPendidikanController extends Controller
{
    /**
     * Display the statistics pendidikan page.
     *
     * @return View
     */
    public function index(): View
    {
        return view('presisi.statistik.pendidikan', [
            'detailLink' => url('data-presisi/pendidikan/detail_data'),
            'judul' => 'Pendidikan',
        ]);
    }
}
