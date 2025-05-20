<?php

namespace App\Http\Controllers;

use App\Enums\AccessTypeEnum;
use App\Models\Point;
use App\Services\PemetaanService;
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
            AccessTypeEnum::LOCK->value() => 'Aktif',
            AccessTypeEnum::UNLOCK->value() => 'Tidak Aktif',
        ];

        $simbol = gis_simbols();

        $action = 'Tambah';
        $form_action = '/api/v1/point';
        $point = null;
        $parrent = 0;
        $tipe = AccessTypeEnum::ROOT->value();

        return view('peta.point.form', compact('status', 'action', 'form_action', 'point', 'simbol', 'parrent', 'tipe'));
    }

    public function edit($id = '')
    {
        $status = [
            AccessTypeEnum::LOCK->value() => 'Aktif',
            AccessTypeEnum::UNLOCK->value() => 'Tidak Aktif',
        ];

        $simbol = gis_simbols();

        $action = 'Ubah';
        $form_action = '/api/v1/point/update/'.$id;
        $point = (object) collect((new PemetaanService)->getAllPoint([
            'filter[id]' => $id
        ]))->first();
        
        // $point = Point::findOrFail($id);
        $parrent = 0;
        $tipe = AccessTypeEnum::ROOT->value();

        return view('peta.point.form', compact('status', 'action', 'form_action', 'point', 'simbol', 'parrent', 'tipe'));
    }

    public function sub($parrent = '')
    {
        $status = [
            AccessTypeEnum::LOCK->value() => 'Aktif',
            AccessTypeEnum::UNLOCK->value() => 'Tidak Aktif',
        ];

        $simbol = gis_simbols();

        $action = 'Tambah';
        $form_action = '/api/v1/point';
        $point = null;
        $parrent = $parrent;
        $tipe = AccessTypeEnum::CHILD->value();

        return view('peta.point.form', compact('status', 'action', 'form_action', 'point', 'simbol', 'parrent', 'tipe'));
    }

    public function detail($id)
    {
        $status = [
            AccessTypeEnum::LOCK->value() => 'Aktif',
            AccessTypeEnum::UNLOCK->value() => 'Tidak Aktif',
        ];
        // $point = Point::findOrFail($id);
        $point = (object) collect((new PemetaanService)->getAllPoint([
            'filter[id]' => $id
        ]))->first();

        // dd($point, $status);

        return view('peta.point.detail', compact('id', 'point', 'status'));
    }

    public function lock($id, $val = 1)
    {
        try {
            $data = ['enabled' => $val];
            (new PemetaanService)->pointLock($data, $id);

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
            $data = [
                'nama' => $request->nama,
                'simbol' => $request->simbol ?? 'default.png',
                'parrent' => $request->parrent ?? 0,
                'tipe' => $request->tipe,
                'sumber' => $request->sumber,
            ];
            
            $point = (object) (new PemetaanService)->pointStore($data);

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
            $point = (object) collect((new PemetaanService)->getAllPoint([
                        'filter[id]' => $id
                    ]))->first();
        
            $data = [
                'nama' => $request->nama,
                'simbol' => $request->simbol ?? $point->simbol,
            ];

            $point = (object) (new PemetaanService)->pointUpdate($data, $id);

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
