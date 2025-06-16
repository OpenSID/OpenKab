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
        $migrationName = '2025_06_16_034026_delete_menu_data_kemiskinan';

        $exists = DB::table('migrations')->where('migration', $migrationName)->exists();

        if ($exists) {
            DB::table('migrations')->where('migration', $migrationName)->delete();
        }

        $deleted = DB::table('menus')->where('name', 'Data Kemiskinan')->delete();

        if ($deleted === 0) {
            DB::table('menus')->where('url', '/presisi/sosial')->delete();
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('menus')->insert([
            'id' => '194',
            'menu_type' => '2',
            'name' => 'Data Kemiskinan',
            'icon' => null,
            'url' => '/presisi/sosial',
            'sequence' => '1',
            'position' => 'top',
            'parent_id' => '193',
            'created_at' => '2023-09-27 17:39:33',
        ]);
    }
};
