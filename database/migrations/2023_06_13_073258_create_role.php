<?php


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
        // Role::create(['name' => 'Admin', 'team_id' => 1, 'guard_name' => 'web']);
        $user = User::find(1);
        setPermissionsTeamId(1);
        // $user->guard_name = 'web';
// dd($user->guard_name);
        // dd(Role::findByName('Admin', 'web'));
        dd($user->assignRole('Admin'));
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
