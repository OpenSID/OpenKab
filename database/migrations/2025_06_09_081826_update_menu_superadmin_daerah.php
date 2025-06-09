<?php

use App\Enums\Modul;
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
        $team = Team::where('name', 'superadmin_daerah')->first();

        if (! $team) {
            return;
        }

        // update ke [] dulu, agar tidak berntrok jika sudah di set manual
        $team->update(['menu' => []]);

        // baru update yang baru tanpa pengaturan group
        $filteredMenu = array_map(function ($item) {
            if (! isset($item['submenu'])) {
                return $item;
            }

            $item['submenu'] = array_filter($item['submenu'], function ($submenuItem) {
                return $submenuItem['url'] !== 'pengaturan/groups';
            });

            return $item;
        }, Modul::Menu);

        $team->update(['menu' => $filteredMenu]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $team = Team::where('name', 'superadmin_daerah')->first();
        if ($team) {
            $team->update(['menu' => Modul::Menu]);
        }
    }
};
