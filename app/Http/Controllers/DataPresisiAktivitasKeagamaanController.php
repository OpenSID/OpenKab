<?php

namespace App\Http\Controllers;

use App\Http\Requests\DetailDataPresisiAktivitasKeagamaanRequest;
use Illuminate\View\View;

class DataPresisiAktivitasKeagamaanController extends Controller
{
    public function detailData(DetailDataPresisiAktivitasKeagamaanRequest $request): View
    {
        $judul = $request->input('judul', '');
        $title = htmlspecialchars(strip_tags($judul));

        $filter = $request->input('filter', []);
        $colomn = '';

        if ($request->filled('filter.tipe') && $request->filled('filter.nilai')) {
            $colomn = $filter['tipe'] . ':' . $filter['nilai'];
        }

        return view('data_pokok.data_presisi.aktivitas_keagamaan.detail_data', [
            'title' => $title,
            'colomn' => $colomn
        ]);
    }
}
