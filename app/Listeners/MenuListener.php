<?php

namespace App\Listeners;

use App\Services\ConfigApiService;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class MenuListener
{
    protected $configApiService;

    public function __construct(ConfigApiService $configApiService)
    {
        $this->configApiService = $configApiService;
    }

    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle(BuildingMenu $event)
    {
        $event->menu->addIn('kabupaten', [
            'text' => 'Semua',
            'url' => 'sesi/hapus/kabupaten',
            'active' => ! session()->has('kabupaten'),
            'data' => [
                'kabupaten' => 'Kabupaten',
            ],
        ]);

        // list menu daftar kabupaten

        if (! auth()->user()->hasRole('administrator')) {
            $kabupatens = $this->configApiService->kabupaten([
                'filter[kode_kabupaten]' => session('kabupaten.kode_kabupaten'),
            ]);
        } else {
            $kabupatens = $this->configApiService->kabupaten();
        }

        foreach ($kabupatens as $item) {
            $event->menu->addIn('kabupaten', [
                'classes' => '<style>height: 400px; overflow-y: scroll</style>',
                'text' => $item->nama_kabupaten,
                'url' => "sesi/kabupaten/{$item->kode_kabupaten}",
                'active' => session()->has('kabupaten') ? session('kabupaten.kode_kabupaten') === $item->kode_kabupaten : false,
                'data' => [
                    'kabupaten' => $item->nama_kabupaten,
                ],
            ]);
        }

        // tampilkan jika kabupaten sudah terpilih
        if (session()->has('kabupaten')) {
            $event->menu->addIn('kecamatan', [
                'text' => 'Semua',
                'url' => 'sesi/hapus/kecamatan',
                'active' => ! session()->has('kecamatan'),
                'data' => [
                    'kecamatan' => 'Kecamatan',
                ],
            ]);

            if (session()->has('kabupaten')) {
                $kecamatans = $this->configApiService->kecamatan([
                    'filter[kode_kabupaten]' => session('kabupaten.kode_kabupaten'),
                ]);
            } else {
                $kecamatans = $this->configApiService->kecamatan();
            }

            foreach ($kecamatans as $item) {
                $event->menu->addIn('kecamatan', [
                    'classes' => '<style>height: 400px; overflow-y: scroll</style>',
                    'text' => $item->nama_kecamatan,
                    'url' => "sesi/kecamatan/{$item->kode_kecamatan}",
                    'active' => session()->has('kecamatan') ? session('kecamatan.kode_kecamatan') === $item->kode_kecamatan : false,
                    'data' => [
                        'kecamatan' => $item->nama_kecamatan,
                    ],
                ]);
            }
        }

        // tampilkan jika kecamatan sudah terpilih
        if (session()->has('kecamatan')) {
            $event->menu->addIn('desa', [
                'text' => 'Semua',
                'url' => 'sesi/hapus/desa',
                'active' => ! session()->has('desa'),
                'data' => [
                    'desa' => config('app.sebutanDesa', 'Desa'),
                ],
            ]);

            // list menu daftar desa
            if (session()->has('kecamatan')) {
                $desas = $this->configApiService->desa([
                    'filter[kode_kecamatan]' => session('kecamatan.kode_kecamatan'),
                ]);
            } else {
                $desas = $this->configApiService->desa();
            }

            foreach ($desas as $item) {
                $event->menu->addIn('desa', [
                    'classes' => '<style>height: 400px; overflow-y: scroll</style>',
                    'text' => $item->nama_desa,
                    'url' => "sesi/desa/{$item?->kode_desa}",
                    'active' => session()->has('desa') ? session('desa.kode_desa') === $item->kode_desa : false,
                    'data' => [
                        'desa' => $item->nama_desa,
                    ],
                ]);
            }
        }

        // tambahkan menu dari group
        $user = auth()->user();
        if ($user) {
            $menuTeam = $user->team->first()?->menu_order ?? $user->team->first()?->menu;
            $presisiEnabled = session('presisi_enabled', false);
            if (! $presisiEnabled) {
                $menuTeam = collect($menuTeam)->filter(function ($item) {
                    return ($item['permission'] ?? '') !== 'datapresisi';
                })->values()->all();
            }

            foreach ($menuTeam ?? [] as $menu) {
                $event->menu->add($menu);
            }
        }
    }
}
