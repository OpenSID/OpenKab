<?php

use App\Enums\Modul;
use App\Models\Team;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Menu "Daftar Data Hasil Impor" (fitur 009, issue #1319) untuk tim DataSpasial yang berhak mengimpor.
 *
 * Sumber kebenaran peran adalah matriks di `2026_10_14_000001_create_dataspasial_roles.php`
 * (ROLE_PERMISSIONS), BUKAN izin yang tersimpan saat ini di database. Dari matriks itu, hanya
 * `operator impor` yang memiliki abilitas `impor` (read, write). Karena itu menu ini valid hanya untuk
 * tim tersebut; `admin skema` dan `analis opd` tidak memilikinya. Tim `administrator` mendapat menu
 * lewat `admin:menu-update`.
 *
 * Yang dikerjakan:
 * 1. menambahkan submenu ke grup DataSpasial pada menu tim (jika belum ada);
 * 2. memastikan peran tim memiliki izin `dataspasial-impor-read` (sudah termasuk dalam matriks).
 *
 * `down()` hanya melepas submenu. Izin tidak dicabut, karena izin itu bagian dari matriks peran.
 * IDEMPOTEN: aman dijalankan berulang kali.
 */
return new class extends Migration
{
    /** Tim yang memiliki abilitas `impor` pada matriks peran DataSpasial. */
    private const TIM = ['operator impor'];

    private const PERMISSION = 'dataspasial-impor';

    private const GRUP = 'DataSpasial';

    private const TEXT = 'Daftar Data Hasil Impor';

    public function up(): void
    {
        $submenu = $this->submenu();
        if ($submenu === null) {
            return;
        }

        Permission::findOrCreate(self::PERMISSION.'-read', 'web');

        foreach ($this->tim() as $team) {
            $menu = $this->tambahSubmenu($team->menu, $submenu);
            if ($menu !== null) {
                $team->update(['menu' => $menu]);
            }

            setPermissionsTeamId($team->id);
            $role = $this->peran($team);
            $role?->syncPermissions(array_values(array_unique(array_merge(
                $role->permissions->pluck('name')->all(),
                [self::PERMISSION.'-read'],
            ))));
        }

        setPermissionsTeamId(null);
    }

    public function down(): void
    {
        foreach ($this->tim() as $team) {
            $menu = $this->hapusSubmenu($team->menu);
            if ($menu !== null) {
                $team->update(['menu' => $menu]);
            }
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, Team>
     */
    private function tim()
    {
        return Team::whereIn('name', self::TIM)->get();
    }

    private function peran(Team $team): ?Role
    {
        return Role::where('team_id', $team->id)->where('name', $team->name)->where('guard_name', 'web')->first();
    }

    /**
     * @return array<string, mixed>|null
     */
    private function submenu(): ?array
    {
        foreach (Modul::Menu as $grup) {
            if (($grup['text'] ?? null) !== self::GRUP) {
                continue;
            }
            foreach ($grup['submenu'] ?? [] as $item) {
                if (($item['text'] ?? null) === self::TEXT) {
                    $item[self::PERMISSION.'-read'] = true;
                    $item[self::PERMISSION.'-write'] = false;
                    $item[self::PERMISSION.'-edit'] = false;
                    $item[self::PERMISSION.'-delete'] = false;
                    $item['selected'] = true;

                    return $item;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $submenu
     * @return array<int, array<string, mixed>>|null
     */
    private function tambahSubmenu(mixed $menu, array $submenu): ?array
    {
        if (! is_array($menu)) {
            return null;
        }

        foreach ($menu as $index => $grup) {
            if (($grup['text'] ?? null) !== self::GRUP) {
                continue;
            }

            $submenus = $grup['submenu'] ?? [];
            foreach ($submenus as $ada) {
                if (($ada['text'] ?? null) === self::TEXT) {
                    return $menu;
                }
            }

            $submenus[] = $submenu;
            $grup['submenu'] = array_values($submenus);
            $grup[self::PERMISSION.'-read'] = true;
            $menu[$index] = $grup;

            return $menu;
        }

        return null;
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private function hapusSubmenu(mixed $menu): ?array
    {
        if (! is_array($menu)) {
            return null;
        }

        foreach ($menu as $index => $grup) {
            if (($grup['text'] ?? null) !== self::GRUP) {
                continue;
            }

            $grup['submenu'] = array_values(array_filter(
                $grup['submenu'] ?? [],
                static fn ($item): bool => ($item['text'] ?? null) !== self::TEXT,
            ));
            $menu[$index] = $grup;

            return $menu;
        }

        return null;
    }
};
