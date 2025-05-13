<?php

namespace App\Listeners;

use App\Models\Config;
use App\Models\Setting;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class MenuListener
{
    public $settings;

    public function __construct()
    {
        $this->settings = Setting::pluck('value', 'key');
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
            'url' => 'sesi/hapus',
            'active' => ! session()->has('kabupaten'),
            'data' => [
                'kabupaten' => 'Kabupaten',
            ],
        ]);

        // list menu daftar kabupaten
        Config::query()
            ->selectRaw('max(nama_kabupaten) as nama_kabupaten, max(kode_kabupaten) as kode_kabupaten')
            ->groupBy('kode_kabupaten')
            ->when($this->isOpenKabSiapPakai() && !auth()->user()->hasRole('administrator'), function($query){
                $query->where('kode_kabupaten', session('kabupaten.kode_kabupaten'));
            })
            ->get()
            ->each(function ($item) use ($event) {
                $event->menu->addIn('kabupaten', [
                    'classes' => '<style>height: 400px; overflow-y: scroll</style>',
                    'text' => $item->nama_kabupaten,
                    'url' => "sesi/kabupaten/{$item->kode_kabupaten}",
                    'active' => session()->has('kabupaten') ? session('kabupaten.kode_kabupaten') === $item->kode_kabupaten : false,
                    'data' => [
                        'kabupaten' => $item->nama_kabupaten,
                    ],
                ]);
            });

        // tampilkan jika kabupaten sudah terpilih
        if (session()->has('kabupaten')) {
            $event->menu->addIn('kecamatan', [
                'text' => 'Semua',
                'url' => 'sesi/hapus',
                'active' => ! session()->has('kecamatan'),
                'data' => [
                    'kecamatan' => 'Kecamatan',
                ],
            ]);

            // list menu daftar kecamatan
            Config::query()
                ->when(session()->has('kabupaten'), function ($query) {
                    $query->where('kode_kabupaten', session('kabupaten.kode_kabupaten'));
                })
                ->selectRaw('max(nama_kecamatan) as nama_kecamatan, max(kode_kecamatan) as kode_kecamatan')
                ->groupBy('kode_kecamatan')
                ->get()
                ->each(function ($item) use ($event) {
                    $event->menu->addIn('kecamatan', [
                        'classes' => '<style>height: 400px; overflow-y: scroll</style>',
                        'text' => $item->nama_kecamatan,
                        'url' => "sesi/kecamatan/{$item->kode_kecamatan}",
                        'active' => session()->has('kecamatan') ? session('kecamatan.kode_kecamatan') === $item->kode_kecamatan : false,
                        'data' => [
                            'kecamatan' => $item->nama_kecamatan,
                        ],
                    ]);
                });
        }

        // tampilkan jika kecamatan sudah terpilih
        if (session()->has('kecamatan')) {
            $event->menu->addIn('desa', [
                'text' => 'Semua',
                'url' => 'sesi/hapus',
                'active' => ! session()->has('desa'),
                'data' => [
                    'desa' => 'Desa',
                ],
            ]);

            // list menu daftar desa
            Config::query()
                ->when(session()->has('kecamatan'), function ($query) {
                    $query->where('kode_kecamatan', session('kecamatan.kode_kecamatan'));
                })
                ->get()
                ->each(function ($item) use ($event) {
                    $event->menu->addIn('desa', [
                        'classes' => '<style>height: 400px; overflow-y: scroll</style>',
                        'text' => $item->nama_desa,
                        'url' => "sesi/desa/{$item->kode_desa}",
                        'active' => session()->has('desa') ? session('desa.kode_desa') === $item->kode_desa : false,
                        'data' => [
                            'desa' => $item->nama_desa,
                        ],
                    ]);
                });
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

    protected function isOpenKabSiapPakai()
    {
        return ($this->settings['OpenKab_SiapPakai'] ?? null) === '1';
    }
}
