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
        DB::table('menus')
            ->where('id', '194')
            ->where('name', 'Data Kemiskinan')
            ->where('url', '/presisi/sosial')
            ->delete();
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
