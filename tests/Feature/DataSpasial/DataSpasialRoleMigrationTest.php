<?php

declare(strict_types=1);

use App\Enums\Modul;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(DatabaseTransactions::class);

/**
 * FR-010/FR-011/FR-012: ketiga peran DataSpasial beserta izin menunya tersedia
 * lewat data migration (otomatis saat deploy), idempoten, dan dapat diberikan
 * ke pengguna. Seluruh tes berjalan dalam transaksi (database tidak tercemar).
 */

/**
 * Jalankan `up()` data migration provisioning peran di dalam transaksi tes.
 */
function runDataSpasialRolesMigration(): void
{
    $migration = require database_path('migrations/2026_10_14_000001_create_dataspasial_roles.php');

    $migration->up();
}

beforeEach(function () {
    config([
        'app.databaseGabunganUrl' => 'https://api.example.test',
        'dataspasial.cache_ttl' => 300,
    ]);

    Cache::flush();

    // Pastikan permission dasar hasil `admin:menu-update` sudah ada.
    setPermissionsTeamId(Team::where('name', 'administrator')->first()->id);
    Artisan::call('admin:menu-update');

    runDataSpasialRolesMigration();
});

function permissionsOf(string $teamName): array
{
    $team = Team::where('name', $teamName)->firstOrFail();
    setPermissionsTeamId($team->id);

    $role = Role::where('team_id', $team->id)->where('name', $teamName)->firstOrFail();

    return $role->permissions->pluck('name')->sort()->values()->all();
}

/**
 * @return array<string, mixed>
 */
function teamMenu(string $teamName): array
{
    return Team::where('name', $teamName)->firstOrFail()->menu ?? [];
}

/**
 * @param  array<int, string>  $abilities
 * @return array<int, string>
 */
function abilities(string $menu, array $abilities): array
{
    return array_map(fn (string $a) => "dataspasial-{$menu}-{$a}", $abilities);
}

// ---------------------------------------------------------------------------
// T023 — Ketiga peran tersedia beserta izin
// ---------------------------------------------------------------------------

it('menyediakan tiga peran DataSpasial', function (string $teamName) {
    $team = Team::where('name', $teamName)->first();

    expect($team)->not->toBeNull();

    setPermissionsTeamId($team->id);

    $role = Role::where('team_id', $team->id)->where('name', $teamName)->first();

    expect($role)->not->toBeNull()
        ->and($role->guard_name)->toBe('web')
        ->and($role->permissions->isNotEmpty())->toBeTrue();
})->with(['admin skema', 'operator impor', 'analis opd']);

it('memberikan permission menu parent pada setiap peran', function (string $teamName) {
    expect(permissionsOf($teamName))->toContain('dataspasial');
})->with(['admin skema', 'operator impor', 'analis opd']);

it('memberikan akses penuh menu DataSpasial kepada role administrator', function () {
    $team = Team::where('name', 'administrator')->firstOrFail();
    setPermissionsTeamId($team->id);

    $role = Role::where('team_id', $team->id)->where('name', 'administrator')->firstOrFail();
    $permissions = $role->permissions->pluck('name')->all();

    // Permission diturunkan `admin:menu-update` dari App\Enums\Modul::Menu
    // (grup DataSpasial + tiap submenu × ability).
    foreach ([
        'dataspasial-read', 'dataspasial-write',
        'dataspasial-skema-read', 'dataspasial-skema-write', 'dataspasial-skema-edit', 'dataspasial-skema-delete',
        'dataspasial-peta-read',
        'dataspasial-dasbor-read',
        'dataspasial-impor-read', 'dataspasial-impor-write',
        'dataspasial-monitoring-read',
    ] as $permission) {
        expect($permissions)->toContain($permission);
    }

    $submenuTexts = [];
    foreach (teamMenu('administrator') as $group) {
        if (($group['text'] ?? null) === 'DataSpasial') {
            $submenuTexts = array_column($group['submenu'] ?? [], 'text');
        }
    }

    expect($submenuTexts)->toContain(
        'Skema DataSpasial',
        'Peta Gabungan',
        'Dasbor DataSpasial',
        'Impor Data',
        'Monitoring',
    );
});

// ---------------------------------------------------------------------------
// T036 — Matriks izin (FR-011)
// ---------------------------------------------------------------------------

it('memberikan Admin Skema hak mengelola skema dan membaca halaman lain', function () {
    $permissions = permissionsOf('admin skema');

    foreach (abilities('skema', ['read', 'write', 'edit', 'delete']) as $permission) {
        expect($permissions)->toContain($permission);
    }

    foreach (abilities('peta', ['read']) + abilities('dasbor', ['read']) + abilities('monitoring', ['read']) as $permission) {
        expect($permissions)->toContain($permission);
    }

    expect($permissions)->not->toContain('dataspasial-impor-write')
        ->and($permissions)->not->toContain('dataspasial-impor-read');
});

it('memberikan Operator Impor hak impor tetapi bukan mengelola skema', function () {
    $permissions = permissionsOf('operator impor');

    expect($permissions)->toContain('dataspasial-impor-read')
        ->and($permissions)->toContain('dataspasial-impor-write')
        ->and($permissions)->not->toContain('dataspasial-skema-write')
        ->and($permissions)->not->toContain('dataspasial-skema-edit')
        ->and($permissions)->not->toContain('dataspasial-skema-delete');
});

it('memberikan Analis OPD hanya hak baca tanpa kemampuan tulis', function () {
    $permissions = permissionsOf('analis opd');

    foreach (abilities('peta', ['read']) + abilities('dasbor', ['read']) + abilities('monitoring', ['read']) as $permission) {
        expect($permissions)->toContain($permission);
    }

    foreach ($permissions as $permission) {
        expect($permission)->not->toEndWith('-write')
            ->and($permission)->not->toEndWith('-edit')
            ->and($permission)->not->toEndWith('-delete');
    }
});

it('menyimpan menu grup pada team sesuai matriks izin', function () {
    $adminSkemaTexts = array_column(teamMenu('admin skema')[0]['submenu'] ?? [], 'text');
    $operatorImporTexts = array_column(teamMenu('operator impor')[0]['submenu'] ?? [], 'text');
    $analisTexts = array_column(teamMenu('analis opd')[0]['submenu'] ?? [], 'text');

    expect($adminSkemaTexts)->toContain('Skema DataSpasial')
        ->and($adminSkemaTexts)->not->toContain('Impor Data')
        ->and($operatorImporTexts)->toContain('Impor Data')
        ->and($operatorImporTexts)->not->toContain('Skema DataSpasial')
        ->and($analisTexts)->not->toContain('Skema DataSpasial')
        ->and($analisTexts)->not->toContain('Impor Data');
});

it('menyimpan flag hak akses pada menu team agar tercentang di Pengaturan Grup', function () {
    // Halaman Pengaturan › Grup membaca flag "{permission}-read|write|edit|delete"
    // dari team.menu (lihat resources/views/group/edit_js.blade.php). Tanpa flag
    // ini kotak centang tidak tercentang.
    $adminGroup = null;
    foreach (teamMenu('admin skema') as $group) {
        if (($group['text'] ?? null) === 'DataSpasial') {
            $adminGroup = $group;
        }
    }

    expect($adminGroup)->not->toBeNull()
        ->and($adminGroup['dataspasial-read'])->toBeTrue()
        ->and($adminGroup['selected'])->toBeTrue();

    $skema = collect($adminGroup['submenu'])->firstWhere('permission', 'dataspasial-skema');

    expect($skema)->not->toBeNull()
        ->and($skema['dataspasial-skema-read'])->toBeTrue()
        ->and($skema['dataspasial-skema-write'])->toBeTrue()
        ->and($skema['dataspasial-skema-edit'])->toBeTrue()
        ->and($skema['dataspasial-skema-delete'])->toBeTrue()
        ->and($skema['selected'])->toBeTrue();

    // Analis OPD: read saja pada Peta Gabungan.
    $analisGroup = null;
    foreach (teamMenu('analis opd') as $group) {
        if (($group['text'] ?? null) === 'DataSpasial') {
            $analisGroup = $group;
        }
    }

    $peta = collect($analisGroup['submenu'])->firstWhere('permission', 'dataspasial-peta');

    expect($peta['dataspasial-peta-read'])->toBeTrue()
        ->and($peta['dataspasial-peta-write'])->toBeFalse()
        ->and($peta['dataspasial-peta-edit'])->toBeFalse()
        ->and($peta['dataspasial-peta-delete'])->toBeFalse();
});

it('memperbaiki menu team lama yang belum memuat flag hak akses', function () {
    $team = Team::where('name', 'admin skema')->firstOrFail();

    // Simulasikan menu versi lama: grup & submenu tanpa flag ability.
    $legacy = [[
        'text' => 'DataSpasial',
        'icon' => 'fas fa-map',
        'permission' => 'dataspasial',
        'submenu' => [
            ['text' => 'Skema DataSpasial', 'url' => 'dataspasial/skema', 'permission' => 'dataspasial-skema'],
        ],
    ]];
    $team->update(['menu' => $legacy]);

    runDataSpasialRolesMigration();

    $group = null;
    foreach (teamMenu('admin skema') as $item) {
        if (($item['text'] ?? null) === 'DataSpasial') {
            $group = $item;
        }
    }

    expect($group['dataspasial-read'])->toBeTrue()
        ->and(collect($group['submenu'])->firstWhere('permission', 'dataspasial-skema')['dataspasial-skema-read'])->toBeTrue();
});

// ---------------------------------------------------------------------------
// T037 — Idempotensi
// ---------------------------------------------------------------------------

it('aman dijalankan berulang tanpa menggandakan data', function () {
    $teamsBefore = Team::whereIn('name', ['admin skema', 'operator impor', 'analis opd'])->count();

    $rolesBefore = 0;
    foreach (['admin skema', 'operator impor', 'analis opd'] as $name) {
        $team = Team::where('name', $name)->first();
        setPermissionsTeamId($team->id);
        $rolesBefore += Role::where('team_id', $team->id)->count();
    }

    runDataSpasialRolesMigration();
    runDataSpasialRolesMigration();

    expect(Team::whereIn('name', ['admin skema', 'operator impor', 'analis opd'])->count())->toBe($teamsBefore);

    $rolesAfter = 0;
    foreach (['admin skema', 'operator impor', 'analis opd'] as $name) {
        $team = Team::where('name', $name)->first();
        setPermissionsTeamId($team->id);
        $rolesAfter += Role::where('team_id', $team->id)->count();
    }

    expect($rolesAfter)->toBe($rolesBefore);
});

it('menjaga izin yang sudah ada pada team ketika dijalankan ulang', function () {
    $team = Team::where('name', 'analis opd')->firstOrFail();
    setPermissionsTeamId($team->id);

    $role = Role::where('team_id', $team->id)->where('name', 'analis opd')->firstOrFail();
    $role->givePermissionTo(Permission::findOrCreate('dataspasial-peta-write', 'web'));

    runDataSpasialRolesMigration();

    expect(permissionsOf('analis opd'))->toContain('dataspasial-peta-write');
});

it('membuat ulang permission DataSpasial bila hilang', function () {
    Permission::where('name', 'like', 'dataspasial%')->delete();

    runDataSpasialRolesMigration();

    expect(permissionsOf('admin skema'))->toContain('dataspasial-skema-read');
});

// ---------------------------------------------------------------------------
// T038 — Penugasan pengguna
// ---------------------------------------------------------------------------

it('menegakkan kontrol akses setelah pengguna ditugaskan ke peran', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*' => Http::response([
            'data' => [['type' => 'ds_sektor', 'id' => '1', 'attributes' => []]],
            'meta' => ['pagination' => ['total' => 1, 'count' => 1]],
            'links' => [],
        ], 200),
    ]);

    $team = Team::where('name', 'analis opd')->firstOrFail();
    setPermissionsTeamId($team->id);

    $user = User::factory()->create();
    $user->teams()->attach($team->id, ['id_user' => $user->id, 'id_team' => $team->id]);
    $user->unsetRelation('team')->load('team');
    $user->assignRole(Role::where('team_id', $team->id)->where('name', 'analis opd')->firstOrFail());

    $this->actingAs($user, 'web');

    $this->get(route('dataspasial.peta'))->assertOk();
    $this->get(route('dataspasial.dasbor'))->assertOk();
    $this->get(route('dataspasial.monitoring'))->assertOk();
    $this->get(route('dataspasial.skema'))->assertForbidden();
    $this->get(route('dataspasial.impor'))->assertForbidden();
});

it('memastikan grup DataSpasial ada di App\Enums\Modul::Menu sebagai sumber kebenaran', function () {
    $texts = array_column(Modul::Menu, 'text');

    expect($texts)->toContain('DataSpasial');
});
