<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(DatabaseTransactions::class);

/**
 * FR-014/FR-015/FR-016: setiap kondisi kegagalan menampilkan pesan generik di
 * area konten dengan kerangka halaman utuh (HTTP 200) — bukan halaman error —
 * dan detail teknis tidak pernah dibocorkan ke pengguna.
 */
beforeEach(function () {
    config([
        'app.databaseGabunganUrl' => 'https://api.example.test',
        'dataspasial.cache_ttl' => 300,
    ]);

    Setting::updateOrCreate(
        ['key' => 'database_gabungan_api_key'],
        ['name' => 'API Key', 'value' => 'token-uji']
    );

    Cache::flush();

    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
    ]);

    $team = Team::firstOrCreate(['name' => 'analis opd 503'], ['menu' => []]);
    setPermissionsTeamId($team->id);

    $role = Role::firstOrCreate(['name' => 'analis opd 503', 'team_id' => $team->id, 'guard_name' => 'web']);
    $role->syncPermissions([
        Permission::findOrCreate('dataspasial-peta-read', 'web'),
        Permission::findOrCreate('dataspasial-dasbor-read', 'web'),
        Permission::findOrCreate('dataspasial-monitoring-read', 'web'),
    ]);

    $user = User::factory()->create();
    $user->teams()->attach($team->id, ['id_user' => $user->id, 'id_team' => $team->id]);
    $user->unsetRelation('team')->load('team');
    $user->assignRole($role);

    $this->actingAs($user, 'web');
});

/**
 * @return array<int, string>
 */
function dataspasialRoutes(): array
{
    return ['dataspasial.peta'];
}

// ---------------------------------------------------------------------------
// T043 — 503 modul belum terpasang
// ---------------------------------------------------------------------------

it('menampilkan pesan data sementara tidak tersedia saat modul belum terpasang', function (string $route) {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*' => Http::response([
            'success' => false,
            'message' => 'Modul DataSpasial belum terpasang atau versinya tidak cocok',
        ], 503),
    ]);

    $response = $this->get(route($route));

    $response->assertOk()
        ->assertSee('Data sementara tidak tersedia')
        ->assertSee('dataspasial-unavailable', false);
})->with(dataspasialRoutes());

// ---------------------------------------------------------------------------
// T044 — Timeout
// ---------------------------------------------------------------------------

it('menampilkan pesan yang sama saat API melewati batas waktu', function (string $route) {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*' => fn () => throw new ConnectionException('cURL error 28: Operation timed out'),
    ]);

    $this->get(route($route))
        ->assertOk()
        ->assertSee('Data sementara tidak tersedia');
})->with(dataspasialRoutes());

// ---------------------------------------------------------------------------
// T045 — Konfigurasi tidak lengkap
// ---------------------------------------------------------------------------

it('menampilkan pesan yang sama saat konfigurasi database gabungan tidak lengkap', function (string $route) {
    Setting::where('key', 'database_gabungan_api_key')->delete();

    $this->get(route($route))
        ->assertOk()
        ->assertSee('Data sementara tidak tersedia');
})->with(dataspasialRoutes());

// ---------------------------------------------------------------------------
// T046 — Empty state (FR-015)
// ---------------------------------------------------------------------------

it('menampilkan keadaan kosong dan bukan pesan gagal saat API sukses tanpa data', function (string $route) {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*' => Http::response([
            'data' => [],
            'meta' => ['pagination' => ['total' => 0, 'count' => 0, 'per_page' => 10, 'current_page' => 1, 'total_pages' => 1]],
            'links' => [],
        ], 200),
    ]);

    $response = $this->get(route($route));

    $response->assertOk()
        ->assertSee('Belum ada data')
        ->assertSee('dataspasial-empty', false)
        ->assertDontSee('Data sementara tidak tersedia');
})->with(dataspasialRoutes());

it('menampilkan isi halaman saat API sukses berisi data', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*' => Http::response([
            'data' => [['type' => 'ds_adopsi', 'id' => '1', 'attributes' => ['status_adopsi' => 'terbaru', 'perkuliahan' => '2025-06']]],
            'meta' => ['pagination' => ['total' => 1, 'count' => 1]],
            'links' => [],
        ], 200),
    ]);

    $this->get(route('dataspasial.monitoring'))
        ->assertOk()        
        ->assertDontSee('Belum ada data');
});

// ---------------------------------------------------------------------------
// T047 — Kerangka halaman tetap utuh
// ---------------------------------------------------------------------------

it('tetap menampilkan navigasi dan kerangka halaman saat API bermasalah', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*' => Http::response(['success' => false, 'message' => 'Modul DataSpasial belum terpasang'], 503),
    ]);

    $content = $this->get(route('dataspasial.peta'))->assertOk()->getContent();

    // Layout adminlte tetap dirender utuh: judul halaman, navigasi sidebar,
    // dan elemen kerangka halaman tetap ada meski data tidak bisa diperoleh.
    expect($content)->toContain('Peta Gabungan')
        ->and($content)->toContain('logout')
        ->and($content)->toContain('content-header')
        ->and($content)->toContain('<body')
        ->and($content)->toContain('Data sementara tidak tersedia');
});

it('tidak pernah menampilkan stack trace atau jejak exception', function (string $route) {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*' => fn () => throw new ConnectionException('cURL error 28: Operation timed out after 10001 ms with /var/www/secret/path.php'),
    ]);

    $content = $this->get(route($route))->assertOk()->getContent();

    expect($content)
        ->not->toContain('cURL error')
        ->not->toContain('/var/www/secret')
        ->not->toContain('Stack trace')
        ->not->toContain('#0 /');
})->with(dataspasialRoutes());

// ---------------------------------------------------------------------------
// T048 — Log tanpa membocorkan detail ke pengguna
// ---------------------------------------------------------------------------

it('tidak menampilkan pesan teknis API kepada pengguna tetapi mencatatnya di log', function () {
    Log::spy();

    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*' => Http::response([
            'success' => false,
            'message' => 'Modul DataSpasial belum terpasang atau versinya tidak cocok',
        ], 503),
    ]);

    $content = $this->get(route('dataspasial.peta'))->assertOk()->getContent();

    expect($content)->not->toContain('Modul DataSpasial belum terpasang');

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $message, array $context = []) => str_contains($message, 'DataSpasial')
            && ($context['reason'] ?? null) === 'module_not_installed');
});
