<?php

namespace App\Http\Controllers;

use App\Http\Repository\SettingRepository;
use App\Http\Requests\CreateSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Http\Transformers\SettingTransformer;
use App\Models\Setting;
use App\Models\SettingModul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class OpenDKController extends AppBaseController
{
    /** @var SettingRepository */
    private $settingRepository;

    protected $permission = 'pengaturan-opendk';

    public function __construct(SettingRepository $settingRepo)
    {
        $this->settingRepository = $settingRepo;
    }

    /**
     * Display a listing of the Setting.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->fractal($this->settingRepository->listSetting(), new SettingTransformer, 'settings')->respond();
        }

        $listPermission = $this->generateListPermission();

        return view('opendk.index')->with($listPermission);
    }    
        

    /**
     * Update the specified Setting in storage.
     */
    public function update($id, UpdateSettingRequest $request)
    {
        if (empty(SettingModul::where('url', 'like', '%prodeskel%')->first()) && $request->value == 'presisi') {
            $setting = Setting::where('key', 'home_page')->where('value', 'presisi')->first();

            if ($setting) {
                $setting->update(['value' => 'default']);
            }

            Session::flash('alert', 'Pengaturan halaman utama dasbor presisi tidak dapat diaktifkan, mohon pastikan prodeskel pada OpenSID harus terpasang.');

            return redirect(route('opendk.index'));
        }

        $setting = $this->settingRepository->find($id);

        if (empty($setting)) {
            Session::flash('error', 'Setting tidak ditemukan');

            return redirect(route('opendk.index'));
        }

        $setting = $this->settingRepository->update($request->all(), $id);

        Session::flash('success', 'Setting berhasil diupdate.');

        return redirect(route('opendk.index'));
    }    

    protected function getOptionItems($id = null)
    {
        return [];
    }
}
