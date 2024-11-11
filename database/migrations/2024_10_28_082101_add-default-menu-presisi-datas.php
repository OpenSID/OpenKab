<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Insert data baru tanpa tag <i> di kolom name
        DB::table('menus')->insert([
            ['id' => '192', 'menu_type' => '2', 'name' => 'Demografi', 'icon' => 'fa-solid fa-chart-column', 'url' => '/presisi', 'sequence' => '2', 'position' => 'top', 'parent_id' => null, 'created_at' => '2023-09-27 17:39:33'],
            ['id' => '193', 'menu_type' => '2', 'name' => 'Sosial', 'icon' => 'fas fa-table', 'url' => '/presisi/bantuan', 'sequence' => '3', 'position' => 'top', 'parent_id' => null, 'created_at' => '2023-09-27 17:39:33'],
            ['id' => '194', 'menu_type' => '2', 'name' => 'Data Kemiskinan', 'icon' => null, 'url' => '/presisi/sosial', 'sequence' => '1', 'position' => 'top', 'parent_id' => '193', 'created_at' => '2023-09-27 17:39:33'],
            ['id' => '195', 'menu_type' => '2', 'name' => 'Program Bantuan', 'icon' => null, 'url' => '/presisi/bantuan', 'sequence' => '2', 'position' => 'top', 'parent_id' => '193', 'created_at' => '2023-09-27 17:39:33'],
            ['id' => '196', 'menu_type' => '2', 'name' => 'Kependudukan', 'icon' => 'fas fa-book', 'url' => '/presisi/kependudukan', 'sequence' => '4', 'position' => 'top', 'parent_id' => null, 'created_at' => '2023-09-27 17:39:33'],
            ['id' => '197', 'menu_type' => '2', 'name' => 'Statistik Penduduk', 'icon' => null, 'url' => '/presisi/kependudukan', 'sequence' => '1', 'position' => 'top', 'parent_id' => '196', 'created_at' => '2023-09-27 17:39:33'],
            ['id' => '198', 'menu_type' => '2', 'name' => 'Statistik Keluarga', 'icon' => null, 'url' => '#', 'sequence' => '2', 'position' => 'top', 'parent_id' => '196', 'created_at' => '2023-09-27 17:39:33'],
            ['id' => '199', 'menu_type' => '2', 'name' => 'Statistik RTM', 'icon' => null, 'url' => '#', 'sequence' => '3', 'position' => 'top', 'parent_id' => '196', 'created_at' => '2023-09-27 17:39:33'],
            ['id' => '200', 'menu_type' => '2', 'name' => 'Ekonomi', 'icon' => 'fa fa-solid fa-briefcase', 'url' => '/presisi/ekonomi', 'sequence' => '5', 'position' => 'top', 'parent_id' => null, 'created_at' => '2023-09-27 17:39:33'],
            ['id' => '201', 'menu_type' => '2', 'name' => 'E-Stunting', 'icon' => 'fa fa-solid fa-camera-retro pl-1', 'url' => '/presisi/kesehatan', 'sequence' => '6', 'position' => 'top', 'parent_id' => null, 'created_at' => '2023-09-27 17:39:33'],
            ['id' => '202', 'menu_type' => '2', 'name' => 'Geo Spasial', 'icon' => 'fas fa-map', 'url' => '#', 'sequence' => '7', 'position' => 'top', 'parent_id' => null, 'created_at' => '2023-09-27 17:39:33'],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Menghapus data berdasarkan ID untuk rollback
        DB::table('menus')->whereIn('id', [192, 193, 194, 195, 196, 197, 198, 199, 200, 201, 202])->delete();
    }
};
