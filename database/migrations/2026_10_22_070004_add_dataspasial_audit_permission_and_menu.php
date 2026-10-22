<?php

use App\Enums\Modul;
use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * KAB-08 (OpenSID/OpenKab#1304) — menu "Log Entri" + izin `dataspasial-audit-read`.
 *
 * Migrasi BARU (bukan mengubah `2026_10_14_000001_create_dataspasial_roles.php`)
 * agar riwayat migrasi yang sudah dijalankan di staging tidak berubah.
 *
 * Yang dikerjakan:
 * 1. Membuat permission `dataspasial-audit-read` (induk `dataspasial` sudah ada).
 * 2. Menambahkan submenu "Log Entri" ke grup DataSpasial milik tim/role
 *    DataSpasial yang sudah ada, lengkap dengan flag ability
 *    `{permission}-read|write|edit|delete` + `selected` (bentuk yang dibaca
 *    `App\Listeners\MenuListener` dan UI Pengaturan › Grup).
 * 3. Memberikan izin `dataspasial-audit-read` kepada role DataSpasial.
 *
 * IDEMPOTEN: aman dijalankan berkali-kali (permission di-findOrCreate,
 * submenu di-cek presence, izin di-sync tanpa mencabut yang lain).
 */
return new class extends Migration
{
    /**
     * Permission menu untuk halaman Log Entri.
     */
    private const PERMISSION = 'dataspasial-audit';

    /**
     * Parent permission grup DataSpasial.
     */
    private const PARENT_PERMISSION = 'dataspasial';

    /**
     * Role (nama grup) yang boleh membaca log entri.
     *
     * Ketiganya adalah role DataSpasial yang sudah ada; tidak ada role baru.
     */
    private const ROLES = [
        'admin skema',
        'operator impor',
        'analis opd',
    ];

    public function up(): void
    {
        Permission::findOrCreate(self::PARENT_PERMISSION, 'web');
        Permission::findOrCreate(self::PERMISSION.'-read', 'web');

        $submenu = $this->auditSubmenu();

        if ($submenu === null) {
            // Menu belum terdaftar di `App\Enums\Modul::Menu` → tidak ada yang
            // bisa ditambahkan ke tim (kode & menu harus sinkron).
            return;
        }

        foreach (self::ROLES as $roleName) {
            $this->applyToRole($roleName, $submenu);
        }

        // Sinkronkan menu + permission role `administrator` (membaca Modul::Menu).
        if (Team::where('name', 'administrator')->exists()) {
            Artisan::call('admin:menu-update');
        }
    }

    public function down(): void
    {
        foreach (self::ROLES as $roleName) {
            $team = Team::where('name', $roleName)->first();

            if (! $team) {
                continue;
            }

            $menu = $this->hapusSubmenu($team->menu);

            if ($menu !== null) {
                $team->update(['menu' => $menu]);
            }

            setPermissionsTeamId($team->id);

            $role = Role::where('team_id', $team->id)->where('name', $roleName)->first();

            $role?->revokePermissionTo(self::PERMISSION.'-read');
        }

        setPermissionsTeamId(null);

        if (Team::where('name', 'administrator')->exists()) {
            Artisan::call('admin:menu-update');
        }

        Permission::where('name', self::PERMISSION.'-read')->delete();
    }

    // -----------------------------------------------------------------------

    /**
     * Berikan akses Log Entri pada satu role DataSpasial.
     *
     * @param  array<string, mixed>  $submenu
     */
    private function applyToRole(string $roleName, array $submenu): void
    {
        $team = Team::where('name', $roleName)->first();

        if (! $team) {
            return;
        }

        $menu = $this->tambahSubmenu($team->menu, $submenu);

        if ($menu !== null) {
            $team->update(['menu' => $menu]);
        }

        setPermissionsTeamId($team->id);

        $role = Role::firstOrCreate([
            'name' => $roleName,
            'team_id' => $team->id,
            'guard_name' => 'web',
        ]);

        // Gabungkan (bukan ganti) supaya izin lain yang ditambahkan admin
        // secara lokal tidak hilang.
        $role->syncPermissions(array_values(array_unique(array_merge(
            $role->permissions->pluck('name')->all(),
            [self::PERMISSION.'-read']
        ))));
    }

    /**
     * Submenu "Log Entri" dari `App\Enums\Modul::Menu` + flag ability.
     *
     * @return array<string, mixed>|null
     */
    private function auditSubmenu(): ?array
    {
        foreach (Modul::Menu as $group) {
            if (($group['text'] ?? null) !== 'DataSpasial') {
                continue;
            }

            foreach ($group['submenu'] ?? [] as $item) {
                if (($item['permission'] ?? null) === self::PERMISSION) {
                    return $this->withAbilityFlags($item, ['read']);
                }
            }
        }

        return null;
    }

    /**
     * Tambahkan submenu ke grup DataSpasial pada menu tim.
     *
     * @param  mixed  $menu
     * @param  array<string, mixed>  $submenu
     * @return array<int, array<string, mixed>>|null
     */
    private function tambahSubmenu(mixed $menu, array $submenu): ?array
    {
        $menu = is_array($menu) ? $menu : [];
        $found = false;

        foreach ($menu as $index => $group) {
            if (($group['text'] ?? null) !== 'DataSpasial') {
                continue;
            }

            $submenus = $group['submenu'] ?? [];

            foreach ($submenus as $existing) {
                if (($existing['permission'] ?? null) === self::PERMISSION) {
                    $found = true;

                    break;
                }
            }

            if (! $found) {
                $submenus[] = $submenu;
            }

            // Grup ikut menandai kemampuan yang diturunkan submenu agar kotak
            // centang di UI Pengaturan › Grup tetap konsisten.
            $group['submenu'] = array_values($submenus);
            $group[self::PERMISSION.'-read'] = true;

            $menu[$index] = $group;

            return $menu;
        }

        return $found ? $menu : null;
    }

    /**
     * Hapus submenu Log Entri dari menu tim.
     *
     * @param  mixed  $menu
     * @return array<int, array<string, mixed>>|null
     */
    private function hapusSubmenu(mixed $menu): ?array
    {
        if (! is_array($menu)) {
            return null;
        }

        foreach ($menu as $index => $group) {
            if (($group['text'] ?? null) !== 'DataSpasial') {
                continue;
            }

            $submenus = array_values(array_filter(
                $group['submenu'] ?? [],
                static fn ($item): bool => ($item['permission'] ?? null) !== self::PERMISSION
            ));

            $group['submenu'] = $submenus;
            unset($group[self::PERMISSION.'-read']);

            $menu[$index] = $group;

            return $menu;
        }

        return null;
    }

    /**
     * Tambahkan flag ability (`{permission}-{ability}`) dan `selected`.
     *
     * @param  array<string, mixed>  $item
     * @param  array<int, string>  $abilities
     * @return array<string, mixed>
     */
    private function withAbilityFlags(array $item, array $abilities): array
    {
        $permission = (string) ($item['permission'] ?? '');

        foreach (['read', 'write', 'edit', 'delete'] as $ability) {
            $item[$permission.'-'.$ability] = in_array($ability, $abilities, true);
        }

        $item['selected'] = true;

        return $item;
    }
};
