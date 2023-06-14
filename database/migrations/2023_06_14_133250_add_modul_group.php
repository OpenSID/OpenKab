<?php

use App\Enums\Modul;
use App\Models\Team;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
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
        $team = Team::where('name', 'administrator')->first();
        $team->menu = Modul::Menu;
        $team->save();

        // add role ke admin
        $role = Role::create(
            [
                'name' => 'pengaturan-group',
                'team_id' =>  $team->id,
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
