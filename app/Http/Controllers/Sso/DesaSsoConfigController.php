<?php

namespace App\Http\Controllers\Sso;

use App\Http\Controllers\Controller;
use App\Http\Requests\SsoDesaConfigRequest;
use App\Models\Sso\DesaSsoConfig;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DesaSsoConfigController extends Controller
{
    public function index(): View
    {
        $configs = DesaSsoConfig::query()->orderBy('desa_id')->paginate(20);

        return view('sso.desa_sso_config.index', compact('configs'));
    }

    public function create(): View
    {
        return view('sso.desa_sso_config.form', ['config' => null]);
    }

    public function store(SsoDesaConfigRequest $request): RedirectResponse
    {
        DesaSsoConfig::create($request->validated());

        session()->flash('success', 'Konfigurasi SSO desa berhasil disimpan.');

        return redirect()->route('sso-config.index');
    }

    public function edit(DesaSsoConfig $ssoConfig): View
    {
        return view('sso.desa_sso_config.form', ['config' => $ssoConfig]);
    }

    public function update(SsoDesaConfigRequest $request, DesaSsoConfig $ssoConfig): RedirectResponse
    {
        $ssoConfig->update($request->validated());

        session()->flash('success', 'Konfigurasi SSO desa berhasil diperbarui.');

        return redirect()->route('sso-config.index');
    }

    public function destroy(DesaSsoConfig $ssoConfig): RedirectResponse
    {
        $ssoConfig->delete();

        session()->flash('success', 'Konfigurasi SSO desa berhasil dihapus.');

        return redirect()->route('sso-config.index');
    }
}
