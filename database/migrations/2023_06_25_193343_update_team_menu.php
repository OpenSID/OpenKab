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
        foreach (Team::get() as $team) {
            // foreach ($team['menu'] as $index => $list_menu) {

            $array_menu = array_map(function ($menu) {
                foreach (Modul::permision as $permission) {
                    $menu[$menu['role'].'-'.$permission] = true;
                }

                if (isset($menu['submenu'])) {
                    foreach ($menu['submenu'] as $key => $submenu) {
                        foreach (Modul::permision as $permission) {
                            $submenu[$submenu['role'].'-'.$permission] = true;
                        }
                        $menu['submenu'][$key] = $submenu;
                    }
                }

                return $menu;
            }, $team['menu']);
            $team['menu'] = $array_menu;

            $team->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
