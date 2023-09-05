<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        Setting::firstOrCreate([
            'key' => 'website_enable',
            'name' => 'Pengaturan aktivasi website',
            'value' => 1,
            'type' => 'dropdown',
            'attribute' => json_encode($attribute),
            'description' => 'Pengaturan apakah website aktif atau tidak'
        ]);
    }
}
