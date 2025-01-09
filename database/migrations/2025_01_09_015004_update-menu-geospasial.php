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
        DB::table('menus')->where('name', 'Geo Spasial')->update(['url' => '/presisi/geo-spasial']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
