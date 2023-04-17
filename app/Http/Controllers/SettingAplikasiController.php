<?php

namespace App\Http\Controllers;

use App\Models\SettingAplikasi;
use Illuminate\Http\Request;
use App\Http\Requests\SettingAplikasiRequest;

class SettingAplikasiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SettingAplikasi $setting)
    {
        $warna_tema_admin = SettingAplikasi::where('key', 'warna_tema_admin')->first();
        $warna_tema_admin_option = json_decode($warna_tema_admin->option);
        return view('setting.index', compact('warna_tema_admin', 'warna_tema_admin_option'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SettingAplikasiRequest $request, SettingAplikasi $setting)
    {
        try {
            SettingAplikasi::where('key', 'warna_tema_admin')->update(['value' => $request->warna_tema_admin]);
            return redirect()->route('setting-aplikasi.index')->with('success', 'Data berhasil diubah!');
        } catch (\Exception $e) {
            report($e);
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
