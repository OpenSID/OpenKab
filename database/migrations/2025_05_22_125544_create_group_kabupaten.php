<?php

use App\Models\Enums\StatusEnum;
use App\Models\Team;
use App\Models\User;
use App\Models\UserTeam;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
            'username' => 'kabupaten',
            'name' => 'Admin Kabupaten',
            'email' => 'kabupaten@opendesa.id',
            'password' => 'kabupaten@89!OK',
            'active' => StatusEnum::aktif,
        ]);

        $team = Team::create([
            'name' => 'kabupaten',
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
        $team = Team::where('name', 'kabupaten')->first();
        UserTeam::where('id_team', $team)->delete();
        $user = User::where('username', 'kabupaten')->first();
        $user->delete();
        $team->role()->delete();
        $team->delete();
    }
};
