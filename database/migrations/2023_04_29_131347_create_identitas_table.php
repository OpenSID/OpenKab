<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
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
        Schema::create('identitas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_aplikasi', 100);
            $table->text('deskripsi')->nullable();
            $table->string('favicon', 100)->nullable();
            $table->string('logo', 100)->nullable();
            $table->string('nama_kabupaten', 100);
            $table->string('kode_kabupaten', 100);
            $table->string('nama_provinsi', 100);
            $table->string('kode_provinsi', 100);
            $table->string('sebutan_kab', 100);
            $table->timestamps();
        });

        Artisan::call('db:seed', [
            '--class' => 'IdentitasSeeder',
            '--force' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('identitas');
    }
};
