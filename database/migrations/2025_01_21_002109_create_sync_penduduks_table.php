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
        Schema::create('sync_penduduks', function (Blueprint $table) {
            $table->id();
            $table->integer('config_id')->nullable(true);
            $table->string('nama', 100);
            $table->char('nik', 16);
            $table->integer('id_kk')->nullable(true);
            $table->tinyInteger('kk_level')->nullable(true);
            $table->integer('id_rtm')->nullable(true);
            $table->integer('rtm_level')->nullable(true);
            $table->tinyInteger('sex')->nullable(true);
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->integer('agama_id')->nullable();
            $table->integer('pendidikan_kk_id')->nullable(true);
            $table->integer('pendidikan_id')->nullable(true);
            $table->integer('pendidikan_sedang_id')->nullable(true);
            $table->integer('pekerjaan_id')->nullable(true);
            $table->tinyInteger('status_kawin')->nullable(true);
            $table->integer('warga_negara_id')->nullable(true);
            $table->string('dokumen_pasport', 45)->nullable(true);
            $table->string('dokumen_kitas', 45)->nullable(true);
            $table->string('ayah_nik', 16)->nullable(true);
            $table->string('ibu_nik', 16)->nullable(true);
            $table->string('nama_ayah', 100)->nullable(true);
            $table->string('nama_ibu', 100)->nullable(true);
            $table->string('foto', 255)->nullable(true);
            $table->integer('golongan_darah_id')->nullable(true);
            $table->integer('id_cluster')->nullable(true);
            $table->integer('status')->nullable(true);
            $table->string('alamat_sebelumnya', 255)->nullable(true);
            $table->string('alamat_sekarang', 255)->nullable(true);
            $table->tinyInteger('status_dasar');
            $table->integer('hamil')->nullable(true);
            $table->integer('cacat_id')->nullable(true);
            $table->integer('sakit_menahun_id')->nullable(true);
            $table->string('akta_lahir', 40)->nullable(true);
            $table->string('akta_perkawinan', 40)->nullable(true);
            $table->date('tanggal_perkawinan')->nullable(true);
            $table->string('akta_perceraian', 40)->nullable(true);
            $table->date('tanggal_perceraian')->nullable(true);
            $table->tinyInteger('cara_kb_id')->nullable(true);
            $table->string('telepon', 20)->nullable(true);
            $table->date('tanggal_akhir_pasport')->nullable(true);
            $table->string('no_kk', 30)->nullable(true);
            $table->string('no_kk_sebelumnya', 30)->nullable(true);
            $table->tinyInteger('ktp_el')->nullable(true);
            $table->tinyInteger('status_rekam')->nullable(true);
            $table->string('alamat', 255)->nullable();
            $table->string('dusun', 255)->nullable();
            $table->string('rw', 10)->nullable();
            $table->string('rt', 10)->nullable();
            $table->char('desa_id', 13)->nullable(true);
            $table->char('kecamatan_id', 7)->nullable(true);
            $table->char('kabupaten_id', 4)->nullable(true);
            $table->char('provinsi_id', 2)->nullable(true);
            $table->integer('tahun')->nullable();
            $table->integer('id_pend_desa')->nullable(true);
            $table->dateTime('imported_at')->nullable(true);
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
        Schema::dropIfExists('sync_penduduks');
    }
};
