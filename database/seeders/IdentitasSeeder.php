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
            'nama_aplikasi' => 'OpenKab',
            'nama_kabupaten' => 'Belum Ditentukan',
            'kode_kabupaten' => '00.00',
            'nama_provinsi' => 'Belum Ditentukan',
            'kode_provinsi' => '00',
            'sebutan_kab' => 'Kabupaten'
        ];
        if(Schema::hasColumn('identitas', 'sebutan_desa')){
            $defaultData['sebutan_desa'] = 'Desa';
        }
        Identitas::create($defaultData);
    }
}
