<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hak akses wilayah pengguna (fitur 009, issue #1319; dasar issue #1320).
 *
 * Struktur mengikuti tabel `user_region_access` di API-Database-Gabungan
 * (level + region_code), tanpa level `provinsi` karena OpenKab melayani satu kabupaten.
 * Satu pengguna boleh punya beberapa baris (mis. beberapa kecamatan).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_region_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('level', ['kabupaten', 'kecamatan', 'desa']);
            $table->string('region_code', 20);
            $table->timestamps();

            $table->index(['user_id', 'level']);
            $table->unique(['user_id', 'level', 'region_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_region_access');
    }
};
