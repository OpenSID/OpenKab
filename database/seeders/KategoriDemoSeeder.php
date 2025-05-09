<?php

namespace Database\Seeders;

use App\Services\KategoriService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KategoriDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $sql = <<<'SQL'
        // INSERT INTO kategori (config_id,kategori,tipe,urut,enabled,parrent,slug) VALUES
        //     (NULL,'Berita Baru',1,0,1,0,'Berita-Baru'),
        //     (NULL,'Bene',1,0,1,0,'Bene'),
        //     (NULL,'Rapat',1,0,1,0,'Rapat'),
        //     (NULL,'Umum',1,0,1,0,'Umum'),
        //     (NULL,'Desa',1,0,1,2,'Desa')
        // SQL;

        // DB::connection('openkab')->statement($sql);

        $data = [
            ['config_id' => null, 'kategori' => 'Berita Baru', 'tipe' => 1, 'urut' => 0, 'enabled' => 1, 'parrent' => 0, 'slug' => 'Berita-Baru'],
            ['config_id' => null, 'kategori' => 'Bene', 'tipe' => 1, 'urut' => 0, 'enabled' => 1, 'parrent' => 0, 'slug' => 'Bene'],
            ['config_id' => null, 'kategori' => 'Rapat', 'tipe' => 1, 'urut' => 0, 'enabled' => 1, 'parrent' => 0, 'slug' => 'Rapat'],
            ['config_id' => null, 'kategori' => 'Umum', 'tipe' => 1, 'urut' => 0, 'enabled' => 1, 'parrent' => 0, 'slug' => 'Umum'],
            ['config_id' => null, 'kategori' => 'Desa', 'tipe' => 1, 'urut' => 0, 'enabled' => 1, 'parrent' => 2, 'slug' => 'Desa'],
        ];

        (new KategoriService)->store($data);

        $this->command->info('Isi data kategori berita');
    }
}
