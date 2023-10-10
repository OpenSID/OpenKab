<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        $sql = <<<SQL
        INSERT INTO kategori (config_id,kategori,tipe,urut,enabled,parrent,slug) VALUES
            (NULL,'Berita Baru',1,0,1,0,'Berita-Baru'),
            (NULL,'Bene',1,0,1,0,'Bene'),
            (NULL,'Rapat',1,0,1,0,'Rapat'),
            (NULL,'Umum',1,0,1,0,'Umum'),
            (NULL,'Desa',1,0,1,2,'Desa')
SQL;
        DB::connection('openkab')->statement($sql);
        $this->command->info('Isi data kategori berita');
    }
}
