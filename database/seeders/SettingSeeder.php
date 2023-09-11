<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attribute = [
            ['text' => 'Tidak Aktif', 'value' => 0],
            ['text' => 'Aktif', 'value' => 1],
        ];
        $website = Setting::where(['key' => 'website_enable'])->first();
        if (!$website) {
            Setting::create([
                'key' => 'website_enable',
                'name' => 'Pengaturan aktivasi website',
                'value' => 1,
                'type' => 'dropdown',
                'attribute' => $attribute,
                'description' => 'Pengaturan apakah website aktif atau tidak',
            ]);
        }

        $statistik = Setting::where(['key' => 'statistik_website'])->first();
        if (! $statistik) {
            Setting::create([
                'key' => 'statistik_website',
                'name' => 'Pengaturan halaman statistik di website',
                'value' => 1,
                'type' => 'dropdown',
                'attribute' => $attribute,
                'description' => 'Pengaturan apakah statistik ditampilkan di website atau tidak',
            ]);
        }
    }
}
