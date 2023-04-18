<?php

namespace App\Listeners;

use App\Models\Config;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class MenuListener
{
    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(BuildingMenu $event)
    {
        $event->menu->addIn('kecamatan', [
            'text' => 'Semua',
            'url' => 'sesi/hapus',
            'active' => ! session()->has('kecamatan'),
        ]);

        // list menu daftar kecamatan
        Config::query()
            ->selectRaw('max(nama_kecamatan) as nama_kecamatan, max(kode_kecamatan) as kode_kecamatan')
            ->groupBy('kode_kecamatan')
            ->get()
            ->each(function ($item) use ($event) {
                $event->menu->addIn('kecamatan', [
                    'classes' => '<style>height: 400px; overflow-y: scroll</style>',
                    'text' => $item->nama_kecamatan,
                    'url' => "sesi/kecamatan/{$item->kode_kecamatan}",
                    'active' => session()->has('kecamatan') ? session('kecamatan.kode_kecamatan') === $item->kode_kecamatan : false,
                ]);
            });

        // tampilkan jika kecamatan sudah terpilih
        if (session()->has('kecamatan')) {
            $event->menu->addIn('desa', [
                'text' => 'Semua',
                'url' => 'sesi/hapus',
                'active' => ! session()->has('desa'),
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
                    ]);
                });
        }
    }
}
