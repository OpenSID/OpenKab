<?php

namespace Database\Seeders;

use App\Models\Identitas;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class IdentitasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $defaultData = [
            'nama_aplikasi' => 'Simatik',
            'nama_kabupaten' => 'KOTA BIMA',
            'kode_kabupaten' => '52.72',
            'nama_provinsi' => 'Nusa Tenggara Barat',
            'kode_provinsi' => '52',
            'sebutan_kab' => 'Kota'
        ];
        if(Schema::hasColumn('identitas', 'sebutan_desa')){
            $defaultData['sebutan_desa'] = 'Kelurahan';
        }
        return Identitas::create($defaultData);
    }
}
