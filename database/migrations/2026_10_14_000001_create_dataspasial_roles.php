<?php

use App\Enums\Modul;
use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Matriks peran <-> izin menu (research.md R6).
     *
     * `read` dipakai untuk akses tampilan; `write`/`edit`/`delete` hanya
     * diberikan kepada peran yang memang mengelola.
     *
     * @var array<string, array<string, array<int, string>>>
     */
    private const ROLE_PERMISSIONS = [
        'admin skema' => [
            'menu' => ['skema', 'peta', 'dasbor', 'monitoring'],
            'abilities' => [
                'skema' => ['read', 'write', 'edit', 'delete'],
                'peta' => ['read'],
                'dasbor' => ['read'],
                'monitoring' => ['read'],
            ],
        ],

        'operator impor' => [
            'menu' => ['impor', 'peta', 'dasbor', 'monitoring'],
            'abilities' => [
                'impor' => ['read', 'write'],
                'peta' => ['read'],
                'dasbor' => ['read'],
                'monitoring' => ['read'],
            ],
        ],

        'analis opd' => [
            'menu' => ['peta', 'dasbor', 'monitoring'],
            'abilities' => [
                'peta' => ['read'],
                'dasbor' => ['read'],
                'monitoring' => ['read'],
            ],
        ],
    ];

    /**
     * Permission grup induk — selalu diberikan agar menu utama tampil.
     */
    private const PARENT_PERMISSION = 'dataspasial';

    /**
     * Sediakan peran DataSpasial beserta izin menunya (FR-010/FR-012).
     *
     * Di OpenKab, "peran" dimodelkan sebagai Team (grup) + Role spatie dengan
     * `team_id` yang sama — mengikuti pola `2023_06_13_073258_create_role.php`
     * dan `App\Console\Commands\updateAdminMenu` (research.md R5).
     *
     * Dijalankan sebagai data migration agar otomatis tersedia saat deploy
     * (`php artisan migrate`). IDEMPOTEN: `Team::firstOrCreate`,
     * `Role::firstOrCreate`, dan `Permission::findOrCreate`.
     */
    public function up(): void
    {
        $this->ensureAdministratorAccess();

        foreach (self::ROLE_PERMISSIONS as $teamName => $config) {
            $this->ensureRole($teamName, $config);
        }
    }

    /**
     * Cabut peran & izin DataSpasial saat rollback.
     */
    public function down(): void
    {
        foreach (array_keys(self::ROLE_PERMISSIONS) as $teamName) {
            $team = Team::where('name', $teamName)->first();

            if (! $team) {
                continue;
            }

            setPermissionsTeamId($team->id);

            Role::where('team_id', $team->id)->where('name', $teamName)->delete();

            $team->users()->detach();
            $team->delete();
        }

        setPermissionsTeamId(null);

        Permission::where('name', 'like', 'dataspasial%')->delete();
        Artisan::call('admin:menu-update');
    }

    /**
     * Beri role `administrator` akses ke menu DataSpasial.
     *
     * Cukup panggil `admin:menu-update`: command tersebut membaca grup menu
     * DataSpasial dari `App\Enums\Modul::Menu` (wajib sudah terdaftar di sana),
     * menuliskan menu ke team `administrator`, dan menyinkronkan seluruh
     * permission turunannya (`dataspasial-*`) ke role administrator.
     *
     * Dijaga bila team administrator belum ada (mis. basis data sangat awal).
     */
    private function ensureAdministratorAccess(): void
    {
        if (! Team::where('name', 'administrator')->exists()) {
            return;
        }

        Artisan::call('admin:menu-update');
    }

    /**
     * @param  array{menu: array<int, string>, abilities: array<string, array<int, string>>}  $config
     */
    private function ensureRole(string $teamName, array $config): void
    {
        $menu = $this->buildMenu($config['menu'], $config['abilities']);

        $team = Team::firstOrCreate(
            ['name' => $teamName],
            ['menu' => $menu]
        );

        // Menu sudah ada tetapi belum dalam bentuk yang benar (mis. dibuat
        // sebelum penambahan flag ability, atau belum berisi submenu
        // DataSpasial) → perbaiki agar hak akses tampil tercentang di UI dan
        // tidak terhapus saat grup disimpan. Menu yang sudah benar tidak diubah.
        if ($this->needsMenuRepair($team->menu)) {
            $team->update(['menu' => $menu]);
        }

        setPermissionsTeamId($team->id);

        $role = Role::firstOrCreate([
            'name' => $teamName,
            'team_id' => $team->id,
            'guard_name' => 'web',
        ]);

        $permissionNames = $this->permissionNames($config['abilities']);

        // Gabungkan, bukan ganti: menjamin matriks minimum selalu ada, tetapi
        // tidak mencabut izin yang memang ditambahkan admin secara lokal.
        $role->syncPermissions(array_values(array_unique(array_merge(
            $role->permissions->pluck('name')->all(),
            $permissionNames
        ))));
    }

    /**
     * @param  array<string, array<int, string>>  $abilities
     * @return array<int, string>
     */
    private function permissionNames(array $abilities): array
    {
        $names = [Permission::findOrCreate(self::PARENT_PERMISSION, 'web')->name];

        foreach ($abilities as $menu => $menuAbilities) {
            foreach ($menuAbilities as $ability) {
                $names[] = Permission::findOrCreate("dataspasial-{$menu}-{$ability}", 'web')->name;
            }
        }

        return array_values(array_unique($names));
    }

    /**
     * Bangun struktur menu grup DataSpasial berisi hanya submenu yang diizinkan,
     * mengikuti bentuk yang dibaca `App\Listeners\MenuListener`.
     *
     * Setiap item (grup & submenu) menyertakan flag ability
     * `"{permission}-read|write|edit|delete"` dan `selected`, yaitu bentuk yang
     * dipakai halaman Pengaturan › Grup (`resources/views/group/edit_js.blade.php`
     * → `mergeByKey`) untuk mencentang hak akses, dan yang dibaca
     * `App\Http\Controllers\Api\TeamController::collectPermissions()` saat
     * grup disimpan. Tanpa flag ini kotak centang tidak muncul tercentang dan
     * menyimpan grup dari UI akan menghapus izinnya.
     *
     * @param  array<int, string>  $allowedMenus
     * @param  array<string, array<int, string>>  $abilities
     * @return array<int, array<string, mixed>>
     */
    private function buildMenu(array $allowedMenus, array $abilities): array
    {
        $group = $this->dataspasialGroup();

        if ($group === null) {
            return [];
        }

        $allowed = array_flip(array_map(
            fn (string $menu) => 'dataspasial-'.$menu,
            $allowedMenus
        ));

        $submenus = [];
        $groupAbilities = [];

        foreach ($group['submenu'] ?? [] as $item) {
            $permission = $item['permission'] ?? '';

            if (! isset($allowed[$permission])) {
                continue;
            }

            // permission submenu = "dataspasial-{slug}" → slug "skema", dst.
            $slug = (string) preg_replace('/^dataspasial-/', '', $permission);
            $itemAbilities = $abilities[$slug] ?? [];

            $submenus[] = $this->withAbilityFlags($item, $itemAbilities);
            $groupAbilities = array_merge($groupAbilities, $itemAbilities);
        }

        $groupItem = array_merge($group, ['submenu' => array_values($submenus)]);

        return [
            $this->withAbilityFlags($groupItem, array_values(array_unique($groupAbilities))),
        ];
    }

    /**
     * Tambahkan flag ability (`{permission}-{ability}`) dan `selected` ke item
     * menu, sesuai bentuk yang diproduksi UI Pengaturan › Grup.
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

    /**
     * Apakah menu team perlu diperbaiki?
     *
     * Dianggap perlu bila grup DataSpasial tidak ada, tidak punya submenu, atau
     * item grup/submenu belum memuat flag ability (`{permission}-read`) yang
     * dibutuhkan halaman Pengaturan › Grup.
     */
    private function needsMenuRepair(mixed $menu): bool
    {
        foreach ((array) $menu as $group) {
            if (($group['text'] ?? null) !== 'DataSpasial') {
                continue;
            }

            if (empty($group['submenu'])) {
                return true;
            }

            if (! array_key_exists(($group['permission'] ?? '').'-read', $group)) {
                return true;
            }

            foreach ($group['submenu'] as $item) {
                if (! array_key_exists(($item['permission'] ?? '').'-read', $item)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function dataspasialGroup(): ?array
    {
        foreach (Modul::Menu as $menu) {
            if (($menu['text'] ?? null) === 'DataSpasial') {
                return $menu;
            }
        }

        return null;
    }
};
