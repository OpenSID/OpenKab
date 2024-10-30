<?php

use App\Models\CMS\Menu;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Menu::truncate();

        Menu::insert([
            [
                'id' => 1,
                'name' => 'Bagian Organisasi',
                'url' => '/module/org',
                'sequence' => 1,
                'position' => 'top',
                'parent_id' => NULL,
                'is_show' => 1
            ],
            [
                'id' => 2,
                'name' => 'Statistik Penduduk',
                'url' => '#',
                'sequence' => 2,
                'position' => 'top',
                'parent_id' => NULL,
                'is_show' => 1
            ],
            [
                'id' => 3,
                'name' => 'Rentang Umur',
                'url' => '/module/statistik/penduduk/rentang-umur',
                'sequence' => 1,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 4,
                'name' => 'Kategori Umur',
                'url' => '/module/statistik/penduduk/kategori-umur',
                'sequence' => 2,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 5,
                'name' => 'Pendidikan Dalam KK',
                'url' => '/module/statistik/penduduk/pendidikan-dalam-kk',
                'sequence' => 3,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 6,
                'name' => 'Pendidikan Sedang Ditempuh',
                'url' => '/module/statistik/penduduk/pendidikan-sedang-ditempuh',
                'sequence' => 4,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 7,
                'name' => 'Pekerjaan',
                'url' => '/module/statistik/penduduk/pekerjaan',
                'sequence' => 5,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 8,
                'name' => 'Status Perkawinan',
                'url' => '/module/statistik/penduduk/status-perkawinan',
                'sequence' => 6,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 9,
                'name' => 'Agama',
                'url' => '/module/statistik/penduduk/agama',
                'sequence' => 7,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 10,
                'name' => 'Jenis Kelamin',
                'url' => '/module/statistik/penduduk/jenis-kelamin',
                'sequence' => 8,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 11,
                'name' => 'Hubungan Dalam KK',
                'url' => '/module/statistik/penduduk/hubungan-dalam-kk',
                'sequence' => 9,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 12,
                'name' => 'Warga Negara',
                'url' => '/module/statistik/penduduk/warga-negara',
                'sequence' => 10,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 13,
                'name' => 'Status Penduduk',
                'url' => '/module/statistik/penduduk/status-penduduk',
                'sequence' => 11,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 14,
                'name' => 'Golongan Darah',
                'url' => '/module/statistik/penduduk/golongan-darah',
                'sequence' => 12,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 15,
                'name' => 'Penyandang Cacat',
                'url' => '/module/statistik/penduduk/penyandang-cacat',
                'sequence' => 13,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 16,
                'name' => 'Penyakit Menahun',
                'url' => '/module/statistik/penduduk/penyakit-menahun',
                'sequence' => 14,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 17,
                'name' => 'Akseptor KB',
                'url' => '/module/statistik/penduduk/akseptor-kb',
                'sequence' => 15,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 18,
                'name' => 'Akta Kelahiran',
                'url' => '/module/statistik/penduduk/akta-kelahiran',
                'sequence' => 16,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 19,
                'name' => 'Kepemilikan KTP',
                'url' => '/module/statistik/penduduk/ktp',
                'sequence' => 17,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 20,
                'name' => 'Asuransi Kesehatan',
                'url' => '/module/statistik/penduduk/asuransi-kesehatan',
                'sequence' => 18,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 21,
                'name' => 'Status Covid',
                'url' => '/module/statistik/penduduk/status-covid',
                'sequence' => 19,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 22,
                'name' => 'Suku / Etnis',
                'url' => '/module/statistik/penduduk/suku',
                'sequence' => 20,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 23,
                'name' => 'BPJS Ketenagakerjaan',
                'url' => '/module/statistik/penduduk/bpjs-ketenagakerjaan',
                'sequence' => 21,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 24,
                'name' => 'Status Kehamilan',
                'url' => '/module/statistik/penduduk/status-kehamilan',
                'sequence' => 22,
                'position' => 'top',
                'parent_id' => 2,
                'is_show' => 1
            ],
            [
                'id' => 25,
                'name' => 'Statistik Keluarga',
                'url' => '#',
                'sequence' => 23,
                'position' => 'top',
                'parent_id' => NULL,
                'is_show' => 1
            ],
            [
                'id' => 26,
                'name' => 'Kelas Sosial',
                'url' => '/module/statistik/keluarga/kelas-sosial',
                'sequence' => 1,
                'position' => 'top',
                'parent_id' => 25,
                'is_show' => 1
            ],
            [
                'id' => 27,
                'name' => 'Statistik RTM',
                'url' => '#',
                'sequence' => 24,
                'position' => 'top',
                'parent_id' => NULL,
                'is_show' => 1
            ],
            [
                'id' => 28,
                'name' => 'BDT',
                'url' => '/module/statistik/rtm/bdt',
                'sequence' => 1,
                'position' => 'top',
                'parent_id' => 27,
                'is_show' => 1
            ],
            [
                'id' => 29,
                'name' => 'Statistik Bantuan',
                'url' => '#',
                'sequence' => 25,
                'position' => 'top',
                'parent_id' => NULL,
                'is_show' => 1
            ],
            [
                'id' => 30,
                'name' => 'Penerima Bantuan Penduduk',
                'url' => '/module/statistik/bantuan/penduduk',
                'sequence' => 1,
                'position' => 'top',
                'parent_id' => 29,
                'is_show' => 1
            ],
            [
                'id' => 31,
                'name' => 'Penerima Bantuan Keluarga',
                'url' => '/module/statistik/bantuan/keluarga',
                'sequence' => 2,
                'position' => 'top',
                'parent_id' => 29,
                'is_show' => 1
            ],
            [
                'id' => 32,
                'name' => 'Daftar Unduhan',
                'url' => '/module/unduhan',
                'sequence' => 26,
                'position' => 'top',
                'parent_id' => NULL,
                'is_show' => 1
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
