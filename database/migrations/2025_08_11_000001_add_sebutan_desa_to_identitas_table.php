<?php

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
        Schema::table('identitas', function (Blueprint $table) {
            $table->string('sebutan_desa', 100)->default('Desa')->after('sebutan_kab');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('identitas', function (Blueprint $table) {
            $table->dropColumn('sebutan_desa');
        });
    }
};
