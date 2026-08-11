<?php

use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Buat permission sso-audit-read + menu "Audit Akses SSO" secara otomatis.
     *
     * Konteks team di-set karena spatie berjalan dengan mode teams; tanpa itu
     * query role tersaring ke team saat ini (null) sehingga role tidak ditemukan.
     */
    public function up(): void
    {
        $permission = Permission::findOrCreate('sso-audit-read', 'web');

        $team = Team::query()->where('name', 'administrator')->first();

        if ($team) {
            setPermissionsTeamId($team->id);

            $role = Role::query()->where('name', 'administrator')->where('guard_name', 'web')->first();

            if ($role && ! $role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }

        Artisan::call('admin:menu-update');
    }

    /**
     * Hapus permission sso-audit-read saat rollback.
     */
    public function down(): void
    {
        $team = Team::query()->where('name', 'administrator')->first();

        if ($team) {
            setPermissionsTeamId($team->id);

            $role = Role::query()->where('name', 'administrator')->where('guard_name', 'web')->first();

            if ($role) {
                $role->revokePermissionTo('sso-audit-read');
            }
        }

        Permission::where('name', 'sso-audit-read')->where('guard_name', 'web')->delete();
    }
};
