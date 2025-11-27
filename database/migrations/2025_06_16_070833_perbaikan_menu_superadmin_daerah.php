<?php

use App\Enums\Modul;
use App\Models\Enums\StatusEnum;
use App\Models\Team;
use App\Models\User;
use App\Models\UserTeam;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
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
        // 1. Buat user jika belum ada
        $user = User::firstOrCreate(
            ['email' => 'daerah@opendesa.id'],
            [
                'username' => 'superadmin_daerah',
                'name' => 'Superadmin Daerah',
                'password' => Hash::make('daerah@89!OK'),
                'active' => StatusEnum::aktif,
            ]
        );

        // 2. Filter menu, hilangkan submenu 'pengaturan/groups'
        $filteredMenu = array_map(function ($item) {
            if (! isset($item['submenu'])) {
                return $item;
            }

            $item['submenu'] = array_filter($item['submenu'], function ($submenuItem) {
                return ($submenuItem['url'] ?? '') !== 'pengaturan/groups';
            });

            return $item;
        }, Modul::Menu);

        // 3. Buat atau ambil team
        $team = Team::firstOrCreate(
            ['name' => 'superadmin_daerah'],
            ['menu' => $filteredMenu, 'menu_order' => null]
        );

        // Update menu jika team sudah ada (agar perubahan tersimpan)
        $team->update(['menu' => $filteredMenu]);

        // 4. Buat relasi user ke team
        UserTeam::firstOrCreate([
            'id_user' => $user->id,
            'id_team' => $team->id,
        ]);

        // 5. Set context permission berdasarkan team
        setPermissionsTeamId($team->id);

        // 6. Buat atau ambil role
        $role = Role::firstOrCreate([
            'name' => $team->name,
            'team_id' => $team->id,
            'guard_name' => 'web',
        ]);

        // 7. Ambil semua permission kecuali untuk "pengaturan groups"
        $excluded = [
            'pengaturan-group-read',
            'pengaturan-group-create',
            'pengaturan-group-edit',
            'pengaturan-group-write',
            'pengaturan-group-update',
            'pengaturan-group-delete',
        ];

        $permissions = Permission::whereNotIn('name', $excluded)
            ->pluck('name')
            ->toArray();

        // 8. Sinkronisasi permission ke role
        $role->syncPermissions($permissions);

        // 9. Assign role ke user jika belum punya
        if (! $user->hasRole($role->name)) {
            $user->assignRole($role);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $user = User::where('email', 'daerah@opendesa.id')->first();
        $team = Team::where('name', 'superadmin_daerah')->first();

        if ($user && $team) {
            UserTeam::where('id_user', $user->id)->where('id_team', $team->id)->delete();
            $user->delete();
            Role::where('name', $team->name)->where('team_id', $team->id)->delete();
            $team->delete();
        }
    }
};
