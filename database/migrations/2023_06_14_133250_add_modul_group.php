<?php

use App\Enums\Modul;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $team = Team::where('name', 'administrator')->first();
        $team->menu = collect(Modul::Menu)->map(function ($menu) {
            if (isset($menu['submenu'])) {
                $submenu = collect($menu['submenu'])->map(function ($submenu) {
                    $submenu['selected'] = true;

                    return $submenu;
                });
                $menu['submenu'] = $submenu;
            }
            $menu['selected'] = true;

            return $menu;
        });
        $team->save();

        // add role ke admin
        $role = Role::create(
            [
                'name' => 'pengaturan-group',
                'team_id' => $team->id,
                'guard_name' => 'web',
            ]
        );
        setPermissionsTeamId($team->id);

        $user = User::where('username', 'admin')->first();
        $user->guard_name = 'web';
        $user->assignRole($role->id);
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
