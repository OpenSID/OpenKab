<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RtmController extends Controller
{
    public function index()
    {
        $filters = request('filter', [
            'kode_kabupaten' => null,
            'kode_desa' => null,
            'kode_kecamatan' => null,
        ]);

        if (isset($filters['kriteria'])) {
            parse_str($filters['kriteria'], $kriteria);
            unset($filters['kriteria']);
            if ($kriteria) {
                foreach ($kriteria as $key => $value) {
                    $filters[$key] = $value;
                }
            }
        }

        $judul = request('judul', '');

        return view('rtm.index', compact('filters', 'judul'));
    }

    /**
     * Display the specified resource.
     *
     * @param number $no_kk
     */
    public function show($no_kk)
    {
        return view('rtm.detail', compact('no_kk'));
    }

    public function cetak(Request $request)
    {
        return view('rtm.cetak', ['filter' => $request->getQueryString()]);
    }
}
