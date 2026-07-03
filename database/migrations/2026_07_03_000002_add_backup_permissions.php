<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        $permissions = [
            'pengaturan-backup-read',
            'pengaturan-backup-write',
            'pengaturan-backup-edit',
            'pengaturan-backup-delete',
        ];

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $role = Role::where('name', 'administrator')->where('guard_name', 'web')->first();

        if ($role) {
            $role->givePermissionTo($permissions);
        }
    }

    public function down(): void
    {
        Permission::whereIn('name', [
            'pengaturan-backup-read',
            'pengaturan-backup-write',
            'pengaturan-backup-edit',
            'pengaturan-backup-delete',
        ])->delete();
    }
};
