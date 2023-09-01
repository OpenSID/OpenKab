<?php

namespace Database\Seeders;

use App\Models\Config;
use App\Models\SettingAplikasi;
use Illuminate\Database\Seeder;

class SettingTemaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $configId = Config::first()->id;
        $warnaTema = SettingAplikasi::where(['config_id' => $configId, 'key' => 'warna_tema'])->count();
        if (!$warnaTema){
            SettingAplikasi::create([
                'config_id' => $configId,
                'judul' => 'Warna Tema',
                'key' => 'warna_tema',
                'value' => null,
                'keterangan' => 'Warna tema untuk halaman website',
                'jenis' => 'color',
                'option' => null,
                'attribute' => null,
                'kategori' => 'openkab'
            ]);
        }
        $lockTheme = SettingAplikasi::where(['config_id' => $configId, 'key' => 'lock_theme'])->count();
        if (! $lockTheme){
            SettingAplikasi::create([
                'config_id' => $configId,
                'judul' => 'Kunci Tema',
                'key' => 'lock_theme',
                'value' => 0,
                'keterangan' => 'Setting kunci tema website',
                'jenis' => 'option',
                'option' => '{"1":"Aktif","0":"Tidak Aktif"}',
                'attribute' => null,
                'kategori' => 'openkab'
            ]);
        }
    }
}
