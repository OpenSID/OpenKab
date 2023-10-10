<?php

namespace App\Http\Controllers;

use App\Http\Repository\PengaturanRepository;
use App\Http\Repository\SettingRepository;
use App\Http\Requests\CreateSettingRequest;
use App\Http\Requests\UpdateSettingRequest;
use App\Http\Transformers\SettingTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class SettingController extends AppBaseController
{
    /** @var SettingRepository */
    private $settingRepository;
    protected $permission = 'pengaturan-settings';

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
        return view('settings.index')->with($listPermission);
    }

    /**
     * Show the form for creating a new Setting.
     */
    public function create()
    {
        return view('settings.create', $this->getOptionItems());
    }

    /**
     * Store a newly created Setting in storage.
     */
    public function store(CreateSettingRequest $request)
    {
        $input = $request->all();

        $setting = $this->settingRepository->create($input);
        Session::flash('success', 'Setting berhasil disimpan.');

        return redirect(route('settings.index'));
    }

    /**
     * Display the specified Setting.
     */
    public function show($id)
    {
        $setting = $this->settingRepository->find($id);

        if (empty($setting)) {
            Session::flash('error', 'Setting tidak ditemukan');

            return redirect(route('settings.index'));
        }

        return view('settings.show')->with('setting', $setting);
    }

    /**
     * Show the form for editing the specified Setting.
     */
    public function edit($id)
    {
        $setting = $this->settingRepository->find($id);

        if (empty($setting)) {
            Session::flash('error', 'Setting tidak ditemukan');

            return redirect(route('settings.index'));
        }

        return view('settings.edit', $this->getOptionItems($id))->with('setting', $setting);
    }

    /**
     * Update the specified Setting in storage.
     */
    public function update($id, UpdateSettingRequest $request)
    {
        $setting = $this->settingRepository->find($id);

        if (empty($setting)) {
            Session::flash('error', 'Setting tidak ditemukan');

            return redirect(route('settings.index'));
        }

        $setting = $this->settingRepository->update($request->all(), $id);

        Session::flash('success', 'Setting berhasil diupdate.');

        return redirect(route('settings.index'));
    }

    /**
     * Remove the specified Setting from storage.
     *
     * @throws \Exception
     */
    public function destroy($id)
    {
        $setting = $this->settingRepository->find($id);

        if (empty($setting)) {
            Session::flash('error', 'Setting tidak ditemukan');

            return redirect(route('settings.index'));
        }

        $this->settingRepository->delete($id);
        if (request()->ajax()) {
            return $this->sendSuccess('Setting berhasil dihapus.');
        }
        Session::flash('success', 'Setting berhasil dihapus.');

        return redirect(route('settings.index'));
    }

    protected function getOptionItems($id = null)
    {
        return [];
    }
}
