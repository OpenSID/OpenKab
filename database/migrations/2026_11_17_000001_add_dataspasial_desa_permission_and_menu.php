<?php

use App\Enums\Modul;
use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * KAB-09 (API-09 / SID-11) — menu "Status Desa" + izin `dataspasial-desa-read|write`.
 *
 * Migrasi BARU (pola `2026_10_22_070004_add_dataspasial_audit_permission_and_menu.php`)
 * agar riwayat migrasi yang sudah berjalan tidak berubah.
 *
 * Yang dikerjakan:
 * 1. Membuat permission `dataspasial-desa-read` dan `dataspasial-desa-write`.
 * 2. Menambahkan submenu "Status Desa" ke grup DataSpasial milik role DataSpasial
 *    yang sudah ada, lengkap dengan flag ability + `selected`.
 * 3. Memberikan `-read` kepada ketiga role; `-write` hanya kepada `admin skema`.
 *
 * IDEMPOTEN: aman dijalankan berkali-kali.
 */
return new class extends Migration
{
    private const PERMISSION = 'dataspasial-desa';

    private const PARENT_PERMISSION = 'dataspasial';

    /**
     * Role (nama grup) beserta ability yang diberikan.
     *
     * @var array<string, array<int, string>>
     */
    private const ROLES = [
        'admin skema' => ['read', 'write'],
        'operator impor' => ['read'],
        'analis opd' => ['read'],
    ];

    public function up(): void
    {
        Permission::findOrCreate(self::PARENT_PERMISSION, 'web');
        Permission::findOrCreate(self::PERMISSION.'-read', 'web');
        Permission::findOrCreate(self::PERMISSION.'-write', 'web');

        $submenu = $this->submenuStatusDesa();

        if ($submenu === null) {
            // Menu belum terdaftar di `App\Enums\Modul::Menu` (kode & menu harus sinkron).
            return;
        }

        foreach (self::ROLES as $roleName => $abilities) {
            $this->applyToRole($roleName, $this->withAbilityFlags($submenu, $abilities), $abilities);
        }

        if (Team::where('name', 'administrator')->exists()) {
            Artisan::call('admin:menu-update');
        }
    }

    public function down(): void
    {
        foreach (array_keys(self::ROLES) as $roleName) {
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

            $role?->revokePermissionTo([self::PERMISSION.'-read', self::PERMISSION.'-write']);
        }

        setPermissionsTeamId(null);

        if (Team::where('name', 'administrator')->exists()) {
            Artisan::call('admin:menu-update');
        }

        Permission::whereIn('name', [self::PERMISSION.'-read', self::PERMISSION.'-write'])->delete();
    }

    // -----------------------------------------------------------------------

    /**
     * @param  array<string, mixed>  $submenu
     * @param  array<int, string>  $abilities
     */
    private function applyToRole(string $roleName, array $submenu, array $abilities): void
    {
        $team = Team::where('name', $roleName)->first();

        if (! $team) {
            return;
        }

        $menu = $this->tambahSubmenu($team->menu, $submenu, $abilities);

        if ($menu !== null) {
            $team->update(['menu' => $menu]);
        }

        setPermissionsTeamId($team->id);

        $role = Role::firstOrCreate([
            'name' => $roleName,
            'team_id' => $team->id,
            'guard_name' => 'web',
        ]);

        $permissions = array_map(fn (string $a) => self::PERMISSION.'-'.$a, $abilities);

        // Gabungkan (bukan ganti) supaya izin lain yang ditambahkan admin secara lokal tidak hilang.
        $role->syncPermissions(array_values(array_unique(array_merge(
            $role->permissions->pluck('name')->all(),
            $permissions
        ))));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function submenuStatusDesa(): ?array
    {
        foreach (Modul::Menu as $group) {
            if (($group['text'] ?? null) !== 'DataSpasial') {
                continue;
            }

            foreach ($group['submenu'] ?? [] as $item) {
                if (($item['permission'] ?? null) === self::PERMISSION) {
                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * @param  mixed  $menu
     * @param  array<string, mixed>  $submenu
     * @param  array<int, string>  $abilities
     * @return array<int, array<string, mixed>>|null
     */
    private function tambahSubmenu(mixed $menu, array $submenu, array $abilities): ?array
    {
        $menu = is_array($menu) ? $menu : [];

        foreach ($menu as $index => $group) {
            if (($group['text'] ?? null) !== 'DataSpasial') {
                continue;
            }

            $submenus = array_values(array_filter(
                $group['submenu'] ?? [],
                static fn ($item): bool => ($item['permission'] ?? null) !== self::PERMISSION
            ));
            $submenus[] = $submenu;

            $group['submenu'] = $submenus;
            foreach ($abilities as $ability) {
                $group[self::PERMISSION.'-'.$ability] = true;
            }

            $menu[$index] = $group;

            return $menu;
        }

        return null;
    }

    /**
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

            $group['submenu'] = array_values(array_filter(
                $group['submenu'] ?? [],
                static fn ($item): bool => ($item['permission'] ?? null) !== self::PERMISSION
            ));
            unset($group[self::PERMISSION.'-read'], $group[self::PERMISSION.'-write']);

            $menu[$index] = $group;

            return $menu;
        }

        return null;
    }

    /**
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
