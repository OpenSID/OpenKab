<?php

use App\Enums\Modul;
use App\Models\Team;
use App\Models\User;
use App\Models\UserTeam;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
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

        Artisan::call('admin:menu-update');
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
