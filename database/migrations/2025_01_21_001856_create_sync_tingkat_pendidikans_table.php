<?php

use App\Models\Config;
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
        Schema::create('sync_tingkat_pendidikans', function (Blueprint $table) {
            $table->id();
            $table->integer('config_id')->nullable(true);
            $table->char('kecamatan_id', 7);
            $table->char('desa_id', 13);
            $table->integer('semester');
            $table->integer('tahun');
            $table->integer('tidak_tamat_sekolah')->default(0);
            $table->integer('tamat_sd')->default(0);
            $table->integer('tamat_smp')->default(0);
            $table->integer('tamat_sma')->default(0);
            $table->integer('tamat_diploma_sederajat')->default(0);
            $table->integer('import_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sync_tingkat_pendidikans');
    }
};
