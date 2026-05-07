<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

/**
 * Controller untuk Statistik Seni Budaya
 *
 * Menampilkan data statistik seni budaya dengan link ke detail data
 */
class StatistikSenibudayaController extends Controller
{
    /**
     * Menampilkan halaman statistik seni budaya
     */
    public function index(): View
    {
        return view('presisi.statistik.senibudaya', [
            'detailLink' => url('data-presisi/seni-budaya/detail_data'),
            'judul' => 'seni budaya',
        ]);
    }
}
