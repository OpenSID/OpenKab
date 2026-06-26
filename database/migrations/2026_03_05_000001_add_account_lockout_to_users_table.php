<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration adds account lockout tracking columns to the users table.
     * These columns are used to track failed login attempts and implement
     * temporary account lockout for security purposes.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Track failed login attempts for account lockout
            $table->unsignedSmallInteger('failed_login_attempts')->default(0)
                ->after('remember_token')
                ->comment('Number of consecutive failed login attempts');

            // Track when the account was locked due to failed attempts
            $table->timestamp('locked_at')->nullable()
                ->after('failed_login_attempts')
                ->comment('Timestamp when account was locked due to failed login attempts');

            // Track when the lockout expires
            $table->timestamp('lockout_expires_at')->nullable()
                ->after('locked_at')
                ->comment('Timestamp when the account lockout expires');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'failed_login_attempts',
                'locked_at',
                'lockout_expires_at',
            ]);
        });
    }
};
