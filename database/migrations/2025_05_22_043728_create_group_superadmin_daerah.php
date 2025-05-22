<?php

use App\Models\Enums\StatusEnum;
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
        $user = User::create([
            'username' => 'superadmin_daerah',
            'name' => 'Superadmin Daerah',
            'email' => 'daerah@opendesa.id',
            'password' => 'daerah@89!OK',
            'active' => StatusEnum::aktif,
        ]);

        $team = Team::create([
            'name' => 'superadmin_daerah',
            'menu' => [],
            'menu_order' => null,
        ]);

        // joinkan user ke group
        UserTeam::create([
            'id_user' => $user->id,
            'id_team' => $team->id,
        ]);
        // assign role berdasarkan team
        $role = Role::create(
            [
                'name' => $team->name,
                'team_id' => $team->id,
                'guard_name' => 'web',
            ]
        );
        setPermissionsTeamId($team->id);
        $user->assignRole($role);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $team = Team::where('name', 'superadmin_daerah')->first();
        UserTeam::where('id_team', $team)->delete();
        $user = User::where('username', 'superadmin_daerah')->first();
        $user->delete();
        $team->role()->delete();
        $team->delete();
    }
};
