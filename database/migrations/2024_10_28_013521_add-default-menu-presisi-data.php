<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
        DB::table("menus")->insert([
            ["id" => "192","menu_type" => "2", "name" => "<i class='fa-solid fa-chart-column'></i> Demografi", "url" => "/presisi", "sequence" => "2", "position" => "top", "parent_id" => NULL, "created_at" => "2023-09-27 17:39:33"],
            ["id" => "193","menu_type" => "2", "name" => "<i class='fas fa-table'></i> Sosial", "url" => "/presisi/bantuan", "sequence" => "3", "position" => "top", "parent_id" => NULL, "created_at" => "2023-09-27 17:39:33"],
            ["id" => "194","menu_type" => "2", "name" => "Data Kemiskinan", "url" => "/presisi/sosial", "sequence" => "1", "position" => "top", "parent_id" => "193", "created_at" => "2023-09-27 17:39:33"],
            ["id" => "195","menu_type" => "2", "name" => "Program Bantuan", "url" => "/presisi/bantuan", "sequence" => "2", "position" => "top", "parent_id" => "193", "created_at" => "2023-09-27 17:39:33"],
            ["id" => "196","menu_type" => "2", "name" => "<i class='fas fa-book'></i> Kependudukan", "url" => "/presisi/kependudukan", "sequence" => "4", "position" => "top", "parent_id" => NULL, "created_at" => "2023-09-27 17:39:33"],
            ["id" => "197","menu_type" => "2", "name" => "Statistik Penduduk", "url" => "/presisi/kependudukan", "sequence" => "1", "position" => "top", "parent_id" => "196", "created_at" => "2023-09-27 17:39:33"],
            ["id" => "198","menu_type" => "2", "name" => "Statistik Keluarga", "url" => "#", "sequence" => "2", "position" => "top", "parent_id" => "196", "created_at" => "2023-09-27 17:39:33"],
            ["id" => "199","menu_type" => "2", "name" => "Statistik RTM", "url" => "#", "sequence" => "3", "position" => "top", "parent_id" => "196", "created_at" => "2023-09-27 17:39:33"],
            ["id" => "200","menu_type" => "2", "name" => "<i class='fa fa-solid fa-briefcase'></i> Ekonomi", "url" => "/presisi/ekonomi", "sequence" => "5", "position" => "top", "parent_id" => NULL, "created_at" => "2023-09-27 17:39:33"],
            ["id" => "201","menu_type" => "2", "name" => "<i class='fa fa-solid fa-camera-retro pl-1'></i> E-Stunting", "url" => "/presisi/kesehatan", "sequence" => "6", "position" => "top", "parent_id" => NULL, "created_at" => "2023-09-27 17:39:33"],
            ["id" => "202","menu_type" => "2", "name" => "<i class='fas fa-map'></i> Geo Spasial", "url" => "#", "sequence" => "7", "position" => "top", "parent_id" => NULL, "created_at" => "2023-09-27 17:39:33"],
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
