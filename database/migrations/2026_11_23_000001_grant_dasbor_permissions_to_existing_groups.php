<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Menambahkan izin Dasbor dan Dasbor Demografi ke semua group (role) yang sudah ada,
 * supaya akses yang berjalan saat ini tidak hilang (research.md R3).
 *
 * Hanya menambah izin. Tidak menghapus data lain.
 */
return new class extends Migration
{
    private array $izin = ['dasbor-read', 'dasbor-demografi-read'];

    /**
     * Role yang menerima izin. Nama diverifikasi dari migrasi dan kode yang ada
     * (administrator, superadmin_daerah, kabupaten).
     */
    private array $roleTujuan = ['administrator', 'superadmin_daerah', 'kabupaten'];

    public function up(): void
    {
        foreach ($this->izin as $nama) {
            Permission::findOrCreate($nama, 'web');
        }

        $teamIds = DB::table('team')->pluck('id');

        foreach ($teamIds as $teamId) {
            setPermissionsTeamId($teamId);

            Role::query()
                ->where('team_id', $teamId)
                ->where('guard_name', 'web')
                ->whereIn('name', $this->roleTujuan)
                ->get()
                ->each(fn (Role $role) => $role->givePermissionTo($this->izin));
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        $permissionIds = Permission::query()
            ->whereIn('name', $this->izin)
            ->where('guard_name', 'web')
            ->pluck('id');

        DB::table('role_has_permissions')
            ->whereIn('permission_id', $permissionIds)
            ->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
