<?php

namespace App\Listeners;

use App\Models\Config;
use JeroenNoten\LaravelAdminLte\Events\BuildingMenu;

class MenuListener
{
    /**
     * Handle the event.
     *
     * @param object $event
     *
     * @return void
     */
    public function handle(BuildingMenu $event)
    {
        $event->menu->addIn('desa', [
            'text' => 'Semua',
            'url' => 'sesi/hapus',
            'active' => ! session()->has('desa'),
        ]);

        Config::get()->each(function ($item) use ($event) {
            $event->menu->addIn('desa', [
                'text' => $item->nama_desa,
                'url' => "sesi/desa/{$item->kode_desa}",
                'active' => session()->has('desa') ? session('desa.kode_desa') === $item->kode_desa : false,
            ]);
        });
    }
}
