<?php

use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     *
     * @return void
     */
    public function up()
    {
        Artisan::call('admin:menu-update');

        $team = Team::where('name', 'administrator')->first();
        $menuOrder = $team->menu_order ?? [];

        if (!$menuOrder) {
            return;
        }

        // Cek apakah "Pengaturan Peta" sudah ada
        $pengaturanPeta = collect($menuOrder)->firstWhere('text', 'Pengaturan Peta');

        if (!$pengaturanPeta) {
            $pengaturanPeta = [
                'text' => 'Pengaturan Peta',
                'icon' => 'fas fa-map', // Ikon peta
                'url' => '#', // Menu induk, tidak punya URL
                'permission' => 'map-settings',
                'submenu' => [],
            ];
            $menuOrder[] = $pengaturanPeta;
        }

        // Tambahkan submenu "Lokasi" dan "Tipe Lokasi"
        $submenuBaru = [
            [
                'text' => 'Lokasi',
                'icon' => 'far fa-fw fa-circle',
                'url' => 'plan',
                'permission' => 'plan',
            ],
            [
                'text' => 'Tipe Lokasi',
                'icon' => 'far fa-fw fa-circle',
                'url' => 'point',
                'permission' => 'point',
            ],
        ];

        // Masukkan submenu ke "Pengaturan Peta"
        $menuOrder = collect($menuOrder)->map(function ($item) use ($submenuBaru) {
            if ($item['text'] === 'Pengaturan Peta') {
                if (!isset($item['submenu'])) {
                    $item['submenu'] = [];
                }
                foreach ($submenuBaru as $submenu) {
                    if (!collect($item['submenu'])->firstWhere('url', $submenu['url'])) {
                        $item['submenu'][] = $submenu;
                    }
                }
            }
            return $item;
        })->toArray();

        $team->menu_order = $menuOrder;
        $team->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $team = Team::where('name', 'administrator')->first();
        $menuOrder = $team->menu_order ?? [];

        if (!$menuOrder) {
            return;
        }

        // Hapus submenu "Lokasi" dan "Tipe Lokasi"
        $menuOrder = collect($menuOrder)->map(function ($item) {
            if ($item['text'] === 'Pengaturan Peta') {
                $item['submenu'] = collect($item['submenu'])->reject(function ($submenu) {
                    return in_array($submenu['text'], ['Lokasi', 'Tipe Lokasi']);
                })->toArray();

                // Jika submenu kosong, hapus menu utama "Pengaturan Peta"
                if (empty($item['submenu'])) {
                    return null;
                }
            }
            return $item;
        })->filter()->toArray();

        $team->menu_order = $menuOrder;
        $team->save();
    }
};
