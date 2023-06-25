<?php

use App\Enums\Modul;
use App\Models\Team;
use App\Models\User;
use App\Models\UserTeam;
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
        $team = Team::create([
            'name' => 'administrator',
            'menu' => Modul::Menu,
        ]);
        setPermissionsTeamId($team->id);

        $user = User::where('username', 'admin')->first();
        $user->guard_name = 'web';

        UserTeam::create([
            'id_user' => $user->id,
            'id_team' => $team->id,
        ]);

        foreach (Modul::Menu as $main_menu) {
            // buat role
            $role= Role::create(
                [
                    'name' => $main_menu['role'],
                    'team_id' => $team->id,
                    'guard_name' => 'web',
                ]
            );
            $user->assignRole($role->id);

            if (isset($main_menu['submenu'])) {
                foreach ($main_menu['submenu'] as $sub_menu) {
                    $role = Role::create(
                        [
                            'name' => $sub_menu['role'],
                            'team_id' => $team->id,
                            'guard_name' => 'web',
                        ]
                    );
                    $user->assignRole($role->id);
                }
            }
        }
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
