<?php

declare(strict_types=1);

use App\Models\Team;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(DatabaseTransactions::class);

/**
 * KAB-09: menu "Status Desa" + izin `dataspasial-desa-read|write` untuk peran DataSpasial.
 * Seluruh tes berjalan dalam transaksi.
 */
function runDataSpasialDesaMigration(string $arah = 'up'): void
{
    $migration = require database_path('migrations/2026_11_17_000001_add_dataspasial_desa_permission_and_menu.php');

    $migration->{$arah}();
}

function permissionsDesa(string $teamName): array
{
    $team = Team::where('name', $teamName)->firstOrFail();
    setPermissionsTeamId($team->id);

    return Role::where('team_id', $team->id)->where('name', $teamName)->firstOrFail()
        ->permissions->pluck('name')->all();
}

function submenuStatusDesa(string $teamName): ?array
{
    foreach (Team::where('name', $teamName)->firstOrFail()->menu ?? [] as $group) {
        if (($group['text'] ?? null) !== 'DataSpasial') {
            continue;
        }

        foreach ($group['submenu'] ?? [] as $item) {
            if (($item['permission'] ?? null) === 'dataspasial-desa') {
                return $item;
            }
        }
    }

    return null;
}

beforeEach(function () {
    Cache::flush();
    setPermissionsTeamId(Team::where('name', 'administrator')->first()->id);
    Artisan::call('admin:menu-update');

    (require database_path('migrations/2026_10_14_000001_create_dataspasial_roles.php'))->up();
    runDataSpasialDesaMigration();
});

it('memberikan izin baca status desa ke ketiga peran dan izin tulis hanya ke Admin Skema', function () {
    expect(permissionsDesa('admin skema'))->toContain('dataspasial-desa-read', 'dataspasial-desa-write');

    foreach (['operator impor', 'analis opd'] as $peran) {
        expect(permissionsDesa($peran))->toContain('dataspasial-desa-read')
            ->not->toContain('dataspasial-desa-write');
    }
});

it('menambahkan submenu Status Desa beserta flag hak akses', function () {
    $admin = submenuStatusDesa('admin skema');
    $analis = submenuStatusDesa('analis opd');

    expect($admin)->not->toBeNull()
        ->and($admin['url'])->toBe('dataspasial/desa')
        ->and($admin['dataspasial-desa-read'])->toBeTrue()
        ->and($admin['dataspasial-desa-write'])->toBeTrue()
        ->and($analis['dataspasial-desa-write'])->toBeFalse();
});

it('memberikan role administrator akses menu Status Desa lewat Modul::Menu', function () {
    $team = Team::where('name', 'administrator')->firstOrFail();
    setPermissionsTeamId($team->id);
    $permissions = Role::where('team_id', $team->id)->where('name', 'administrator')->firstOrFail()
        ->permissions->pluck('name')->all();

    expect($permissions)->toContain('dataspasial-desa-read', 'dataspasial-desa-write');
});

it('aman dijalankan berulang tanpa menggandakan submenu maupun permission', function () {
    runDataSpasialDesaMigration();

    $jumlah = 0;
    foreach (Team::where('name', 'admin skema')->firstOrFail()->menu as $group) {
        if (($group['text'] ?? null) === 'DataSpasial') {
            $jumlah = count(array_filter($group['submenu'], fn ($i) => ($i['permission'] ?? null) === 'dataspasial-desa'));
        }
    }

    expect($jumlah)->toBe(1)
        ->and(Permission::where('name', 'dataspasial-desa-write')->where('guard_name', 'web')->count())->toBe(1);
});

it('rollback mencabut submenu dan izin status desa', function () {
    runDataSpasialDesaMigration('down');

    expect(submenuStatusDesa('admin skema'))->toBeNull()
        ->and(Permission::where('name', 'dataspasial-desa-read')->exists())->toBeFalse();
});
