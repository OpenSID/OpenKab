<?php

declare(strict_types=1);

use App\Enums\Modul;
use App\Models\Setting;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(DatabaseTransactions::class);

/**
 * FR-009/FR-013: visibilitas menu mengikuti izin peran, dan akses langsung
 * lewat URL ke halaman yang tidak diizinkan HARUS ditolak — bukan sekadar
 * menu disembunyikan.
 */

/**
 * Buat pengguna yang/group berizin pada sekumpulan permission DataSpasial.
 *
 * @param  array<int, string>  $permissions
 */
function actingAsWithTeam(string $teamName, array $permissions): User
{
    $team = Team::firstOrCreate(['name' => $teamName], ['menu' => []]);

    setPermissionsTeamId($team->id);

    $role = Role::firstOrCreate(
        ['name' => $teamName, 'team_id' => $team->id, 'guard_name' => 'web']
    );

    $role->syncPermissions(array_map(
        fn (string $name) => Permission::findOrCreate($name, 'web'),
        $permissions
    ));

    $user = User::factory()->create();
    $user->forceFill(['name' => 'Uji '.$teamName])->save();
    $user->teams()->attach($team->id, ['id_user' => $user->id, 'id_team' => $team->id]);
    $user->unsetRelation('team')->load('team');
    $user->assignRole($role);

    test()->actingAs($user, 'web');

    return $user;
}

/**
 * @return array<int, string>
 */
function permissionNames(string $menu, array $abilities): array
{
    return array_map(fn (string $ability) => "dataspasial-{$menu}-{$ability}", $abilities);
}

function fakeApiSuccess(): void
{
    Http::fake([
        // Konfigurasi wilayah yang dipakai sidebar adminlte (MenuListener).
        '*/api/v1/config/kabupaten*' => Http::response(['data' => []], 200),
        '*/api/v1/config/kecamatan*' => Http::response(['data' => []], 200),
        '*/api/v1/config/desa*' => Http::response(['data' => []], 200),
        // Endpoint DataSpasial.
        '*' => Http::response([
            'data' => [['type' => 'ds_sektor', 'id' => '1', 'attributes' => ['kode' => 'pendidikan', 'nama' => 'Pendidikan']]],
            'meta' => ['pagination' => ['total' => 1, 'count' => 1, 'per_page' => 10, 'current_page' => 1, 'total_pages' => 1]],
            'links' => [],
        ], 200),
    ]);
}

beforeEach(function () {
    config([
        'app.databaseGabunganUrl' => 'https://api.example.test',
        'dataspasial.cache_ttl' => 300,
    ]);

    Setting::updateOrCreate(
        ['key' => 'database_gabungan_api_key'],
        ['name' => 'API Key', 'value' => 'token-uji']
    );

    fakeApiSuccess();
});

// ---------------------------------------------------------------------------
// T026 — Akses langsung lewat URL ditolak bagi peran tanpa izin (FR-013)
// ---------------------------------------------------------------------------

it('menolak akses Analis OPD ke Skema DataSpasial dan Impor Data', function (string $route) {
    actingAsWithTeam('analis opd', permissionNames('peta', ['read']));

    $this->get(route($route))->assertForbidden();
})->with(['dataspasial.skema', 'dataspasial.impor']);

it('mengizinkan Admin Skema membuka Skema DataSpasial', function () {
    actingAsWithTeam('admin skema', permissionNames('skema', ['read', 'write', 'edit', 'delete']));

    $this->get(route('dataspasial.skema'))->assertOk();
});

it('mengizinkan Operator Impor membuka Impor Data', function () {
    actingAsWithTeam('operator impor', permissionNames('impor', ['read', 'write']));

    $this->get(route('dataspasial.impor'))->assertOk();
});

it('mengizinkan Analis OPD membuka ketiga halaman baca', function (string $route) {
    actingAsWithTeam('analis opd', array_merge(
        permissionNames('peta', ['read']),
        permissionNames('dasbor', ['read']),
        permissionNames('monitoring', ['read']),
    ));

    $this->get(route($route))->assertOk();
})->with(['dataspasial.peta', 'dataspasial.dasbor', 'dataspasial.monitoring']);

it('menolak seluruh halaman DataSpasial bagi pengguna tanpa peran DataSpasial', function (string $route) {
    actingAsWithTeam('staff biasa', ['penduduk-read']);

    $this->get(route($route))->assertForbidden();
})->with([
    'dataspasial.skema',
    'dataspasial.peta',
    'dataspasial.dasbor',
    'dataspasial.impor',
    'dataspasial.monitoring',
]);

it('menolak Admin Skema membuka Impor Data', function () {
    actingAsWithTeam('admin skema', permissionNames('skema', ['read', 'write', 'edit', 'delete']));

    $this->get(route('dataspasial.impor'))->assertForbidden();
});

it('menolak Operator Impor membuka Skema DataSpasial', function () {
    actingAsWithTeam('operator impor', permissionNames('impor', ['read', 'write']));

    $this->get(route('dataspasial.skema'))->assertForbidden();
});

// ---------------------------------------------------------------------------
// T027 — Menu tidak tampil bagi peran tanpa izin
// ---------------------------------------------------------------------------

it('menyembunyikan menu Skema dan Impor dari Analis OPD', function () {
    // Menu grup disimpan per team; seeder hanya memasukkan submenu yang diizinkan.
    $menu = dataspasialMenuFor([
        'dataspasial-peta-read',
        'dataspasial-dasbor-read',
        'dataspasial-monitoring-read',
    ]);

    $visibleTexts = array_column($menu[0]['submenu'], 'text');

    expect($visibleTexts)->toContain('Peta Gabungan', 'Dasbor DataSpasial', 'Monitoring')
        ->and($visibleTexts)->not->toContain('Skema DataSpasial')
        ->and($visibleTexts)->not->toContain('Impor Data');
});

it('menyembunyikan menu Impor dari Admin Skema dan menu Skema dari Operator Impor', function (array $allowed, array $hidden) {
    $menu = dataspasialMenuFor($allowed);
    $visibleTexts = array_column($menu[0]['submenu'], 'text');

    foreach ($hidden as $text) {
        expect($visibleTexts)->not->toContain($text);
    }
})->with([
    [
        ['dataspasial-skema-read', 'dataspasial-peta-read', 'dataspasial-dasbor-read', 'dataspasial-monitoring-read'],
        ['Impor Data'],
    ],
    [
        ['dataspasial-impor-read', 'dataspasial-peta-read', 'dataspasial-dasbor-read', 'dataspasial-monitoring-read'],
        ['Skema DataSpasial'],
    ],
]);

it('menampilkan kelima menu DataSpasial pada administrator', function () {
    $group = dataspasialMenuGroup();

    expect(array_column($group['submenu'], 'text'))->toBe([
        'Skema DataSpasial',
        'Peta Gabungan',
        'Dasbor DataSpasial',
        'Impor Data',
        'Monitoring',
    ]);
});

// ---------------------------------------------------------------------------
// T028 — Struktur menu di App\Enums\Modul::Menu
// ---------------------------------------------------------------------------

it('mendaftarkan grup DataSpasial dengan lima submenu berizin', function () {
    $group = dataspasialMenuGroup();

    expect($group['text'])->toBe('DataSpasial')
        ->and($group['permission'])->toBe('dataspasial')
        ->and(count($group['submenu']))->toBe(5);

    foreach ($group['submenu'] as $item) {
        expect($item['permission'])->toStartWith('dataspasial-')
            ->and($item['url'])->toStartWith('dataspasial/')
            ->and($item['text'])->not->toBeEmpty();
    }
});

it('memetakan setiap submenu ke URL yang sesuai', function () {
    $group = dataspasialMenuGroup();
    $actual = [];

    foreach ($group['submenu'] as $item) {
        $actual[$item['text']] = $item['url'];
    }

    expect($actual)->toBe([
        'Skema DataSpasial' => 'dataspasial/skema',
        'Peta Gabungan' => 'dataspasial/peta',
        'Dasbor DataSpasial' => 'dataspasial/dasbor',
        'Impor Data' => 'dataspasial/impor',
        'Monitoring' => 'dataspasial/monitoring',
    ]);
});

it('menempatkan grup DataSpasial di dalam App\Enums\Modul::Menu', function () {
    $texts = array_column(Modul::Menu, 'text');

    expect($texts)->toContain('DataSpasial');
});

/**
 * Ambil grup menu DataSpasial dari App\Enums\Modul::Menu.
 *
 * @return array<string, mixed>
 */
function dataspasialMenuGroup(): array
{
    foreach (Modul::Menu as $menu) {
        if (($menu['text'] ?? null) === 'DataSpasial') {
            return $menu;
        }
    }

    test()->fail('Grup menu DataSpasial tidak ditemukan di App\Enums\Modul::Menu.');
}

/**
 * Bangun grup menu DataSpasial berisi hanya submenu yang diizinkan.
 *
 * @param  array<int, string>  $permissions
 * @return array<int, array<string, mixed>>
 */
function dataspasialMenuFor(array $permissions): array
{
    $group = dataspasialMenuGroup();

    return [
        array_merge($group, [
            'submenu' => array_values(array_filter(
                $group['submenu'],
                fn (array $item) => in_array($item['permission'].'-read', $permissions, true)
            )),
        ]),
    ];
}
