<?php

namespace Database\Seeders;

use App\Models\Identitas;
use Illuminate\Database\Seeder;

class IdentitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        return Identitas::create([
            'nama_aplikasi' => 'Simatik',
            'nama_kabupaten' => 'KOTA BIMA',
            'kode_kabupaten' => '52.72',
            'nama_provinsi' => 'Nusa Tenggara Barat',
            'kode_provinsi' => '52',
            'sebutan_kab' => 'Kota',
        ]);
    }
}
