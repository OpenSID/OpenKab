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
        Schema::create('openkab_sso_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('desa_id', 20);
            $table->timestamp('attempt_time');
            $table->string('status', 20);
            $table->string('reason_if_failed', 255)->nullable();
            $table->string('ip_address', 45);
            $table->string('user_agent', 255);
            $table->string('token_fingerprint', 64)->nullable();
            $table->timestamps();

            $table->index(['admin_id', 'attempt_time'], 'idx_admin_time');
            $table->index(['desa_id', 'attempt_time'], 'idx_desa_time');
            $table->index(['status', 'attempt_time'], 'idx_status_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('openkab_sso_logs');
    }
};
