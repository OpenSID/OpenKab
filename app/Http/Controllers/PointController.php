<?php

namespace App\Http\Controllers;

use App\Models\Point;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class PointController extends Controller
{
    public function index()
    {
        return view('peta.point.index');
    }

    public function form()
    {
        $status = [
            Point::LOCK => 'Aktif',
            Point::UNLOCK => 'Tidak Aktif',
        ];

        $simbol = gis_simbols();

        $action = 'Tambah';
        $form_action = url('api/v1/point');
        $point = null;
        $parrent = 0;
        $tipe = Point::ROOT;

        return view('peta.point.form', compact('status', 'action', 'form_action', 'point', 'simbol', 'parrent', 'tipe'));
    }

    public function edit($id = '')
    {
        $status = [
            Point::LOCK => 'Aktif',
            Point::UNLOCK => 'Tidak Aktif',
        ];

        $simbol = gis_simbols();

        $action = 'Ubah';
        $form_action = url('api/v1/point/update', $id);
        $point = Point::findOrFail($id);
        $parrent = 0;
        $tipe = Point::ROOT;

        return view('peta.point.form', compact('status', 'action', 'form_action', 'point', 'simbol', 'parrent', 'tipe'));
    }

    public function sub($parrent = '')
    {
        $status = [
            Point::LOCK => 'Aktif',
            Point::UNLOCK => 'Tidak Aktif',
        ];

        $simbol = gis_simbols();

        $action = 'Tambah';
        $form_action = url('api/v1/point');
        $point = null;
        $parrent = $parrent;
        $tipe = Point::CHILD;

        return view('peta.point.form', compact('status', 'action', 'form_action', 'point', 'simbol', 'parrent', 'tipe'));
    }

    public function detail($id)
    {
        $status = [
            Point::LOCK => 'Aktif',
            Point::UNLOCK => 'Tidak Aktif',
        ];
        $point = Point::findOrFail($id);

        return view('peta.point.detail', compact('id', 'point', 'status'));
    }

    public function lock($id, $val = 1)
    {
        try {
            Point::findOrFail($id)->update(['enabled' => $val]);

            Session::flash('success', 'Status berhasil diupdate.');

            return redirect(route('point'));
        } catch (\Exception $e) {
            report($e);

            Session::flash('error', 'Status berhasil diupdate.');

            return redirect(route('point'));
        }
    }

    public function store(Request $request)
    {
        // Simpan data ke database
        try {
            $point = Point::create([
                'nama' => $request->nama,
                'simbol' => $request->simbol ?? 'default.png',
                'parrent' => $request->parrent ?? 0,
                'tipe' => $request->tipe,
                'sumber' => $request->sumber,
            ]);

            // Menyimpan pesan sukses ke session
            Session::flash('success', 'Data berhasil disimpan.');
            if ($request->parrent) {
                return redirect(url('point/rincian/'.$point->parrent));
            }

            return redirect(route('point'));
        } catch (\Exception $e) {
            // Menyimpan pesan error ke session
            Session::flash('error', 'Terjadi kesalahan saat menyimpan data.');

            return redirect(route('point'));
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // Cari data berdasarkan ID
            $point = Point::findOrFail($id);

            // Update data di database
            $point->update([
                'nama' => $request->nama,
                'simbol' => $request->simbol ?? $point->simbol,
            ]);

            // Menyimpan pesan sukses ke session
            Session::flash('success', 'Data berhasil diperbarui.');
            if ($point->parrent) {
                return redirect(url('point/rincian/'.$point->parrent));
            }

            return redirect(route('point'));
        } catch (\Exception $e) {
            // Menyimpan pesan error ke session
            Session::flash('error', 'Terjadi kesalahan saat memperbarui data.');

            return redirect(route('point'));
        }
    }
}
