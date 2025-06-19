<?php

use App\Models\Team;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $teams = Team::where('name', '!=', 'administrator')->get();
        if ($teams->isEmpty()) {
            // Jika team sudah ada, tidak perlu membuat ulang
            return;
        }

        $teams->each(function ($item) {
            $menu = collect($item->menu)->filter(function ($menuItem) {
                if (! isset($menuItem['text'])) {
                    return true; // Tetap pertahankan item yang tidak memiliki 'text'
                }

                return $menuItem['text'] !== 'Pengaturan Aplikasi';
            })->values();
            $item->update(['menu' => $menu, 'menu_order' => null]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
};
