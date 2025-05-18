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
        if (! Schema::hasColumn('users', 'kode_kabupaten')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('kode_kabupaten', 100)->nullable()->after('foto');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('users', 'kode_kabupaten')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('kode_kabupaten');
            });
        }
    }
};
