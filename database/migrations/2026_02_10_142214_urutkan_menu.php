<?php

use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // reset urutan menu
        Team::query()->update(['menu_order' => null]);       
        Artisan::call('admin:menu-update'); 
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // tidak bisa dikembalikan
    }
};
