<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('openkab_sso_tokens', function (Blueprint $table) {
            $table->id();
            $table->uuid('jti')->unique();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('desa_id', 20);
            $table->string('token_fingerprint', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->string('consumed_by_ip', 45)->nullable();
            $table->timestamps();

            $table->index(['used_at', 'expires_at'], 'idx_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('openkab_sso_tokens');
    }
};
