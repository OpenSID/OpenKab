<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Artisan::call('admin:menu-update');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
