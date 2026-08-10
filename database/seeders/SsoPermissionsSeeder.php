<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SsoPermissionsSeeder extends Seeder
{
    /**
     * Tambahkan permission audit SSO dan lampirkan ke role administrator (super admin).
     */
    public function run(): void
    {
        $permission = Permission::findOrCreate('sso-audit-read', 'web');

        $role = Role::query()->where('name', 'administrator')->where('guard_name', 'web')->first();

        if ($role) {
            if (! $role->hasPermissionTo($permission)) {
                $role->givePermissionTo($permission);
            }
        }
    }
}
