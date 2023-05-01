<?php

namespace App\Http\Controllers;

use App\Http\Requests\SettingAplikasiRequest;
use App\Models\SettingAplikasi;

class SettingAplikasiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SettingAplikasi $setting)
    {
        if (empty($warna_tema = SettingAplikasi::where('key', 'warna_tema')->first())) {
            $warna_tema = '';
        }

        return view('setting.index', compact('warna_tema'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(SettingAplikasiRequest $request, SettingAplikasi $setting)
    {
        try {
            SettingAplikasi::where('key', 'warna_tema')->update(['value' => $request->warna_tema]);

            return redirect()->route('setting-aplikasi.index')->with('success', 'Data berhasil diubah!');
        } catch (\Exception $e) {
            report($e);

            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
