<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hak akses sektor DataSpasial per pengguna (issue OpenSID/OpenKab#1320).
 *
 * Menyimpan `kode` sektor (stabil), bukan id API. Pengguna tanpa baris tidak melihat sektor apa pun.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_sector_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('sektor_kode', 100);
            $table->timestamps();

            $table->unique(['user_id', 'sektor_kode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sector_access');
    }
};
