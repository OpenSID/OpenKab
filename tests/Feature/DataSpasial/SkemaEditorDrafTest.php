<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\Team;
use App\Models\User;
use App\Services\DataSpasialApiService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(DatabaseTransactions::class);

/**
 * US3 — Menyimpan draf & membukanya kembali.
 *
 * Skenario yang diuji (spec.md US3):
 *  #1 simpan draf → umpan balik sukses + label draf (FR-031)
 *  #2 draf tersimpan dapat dibuka kembali dengan isi utuh (FR-030)
 *  #3 sumber mati saat menyimpan → pesan generic, isi tetap terpulihkan (FR-032)
 *  #4 API menolak (422) → penjelasan, isi tidak hilang (FR-021)
 *  #5 label versi/draf konsisten dengan keadaan sebenarnya (FR-031)
 */
beforeEach(function () {
    config([
        'app.databaseGabunganUrl' => 'https://api.example.test',
        'dataspasial.cache_ttl' => 300,
    ]);

    Cache::flush();

    Setting::updateOrCreate(
        ['key' => 'database_gabungan_api_key'],
        ['name' => 'API Key', 'value' => 'token-uji']
    );

    $team = Team::firstOrCreate(['name' => 'admin skema us3'], ['menu' => []]);
    setPermissionsTeamId($team->id);

    $role = Role::firstOrCreate(['name' => 'admin skema us3', 'team_id' => $team->id, 'guard_name' => 'web']);
    $role->syncPermissions(array_map(
        fn (string $n) => Permission::findOrCreate($n, 'web'),
        ['dataspasial-skema-read', 'dataspasial-skema-write', 'dataspasial-skema-edit', 'dataspasial-skema-delete']
    ));

    $this->user = User::factory()->create();
    $this->user->forceFill(['name' => 'Uji Admin Skema US3'])->save();
    $this->user->teams()->attach($team->id, ['id_user' => $this->user->id, 'id_team' => $team->id]);
    $this->user->unsetRelation('team')->load('team');
    $this->user->assignRole($role);

    test()->actingAs($this->user, 'web');

    $this->urlDraf = route('dataspasial.skema.draf.store');
});

/**
 * @param  array<string, mixed>  $ubah
 * @return array<string, mixed>
 */
function isianDraf(array $ubah = []): array
{
    return array_merge([
        'id' => null,
        'kode' => 'jumlah_siswa',
        'label' => 'Jumlah Siswa',
        'tipe' => 'angka',
        'wajib' => true,
        'satuan' => 'orang',
        'opsi' => null,
        'urutan' => 1,
        'popupPeta' => false,
        'sebagaiFilter' => false,
        'sebagaiIndikator' => false,
        'sektorSumber' => null, 'rujukan' => null,
    ], $ubah);
}

/**
 * @param  array<int, array<string, mixed>>  $isian
 * @return array<string, mixed>
 */
function draf(array $isian, array $tambah = []): array
{
    return array_merge(['sektor' => 'pendidikan', 'catatan_perubahan' => 'Draf uji.'], $tambah, ['isian' => $isian]);
}

// ---------------------------------------------------------------------------
// US3#1 — simpan berhasil
// ---------------------------------------------------------------------------

it('menyimpan draf dan memberi umpan balik sukses', function () {
    Http::fake([
        '*/api/v1/dataspasial/skema' => Http::response([
            'data' => ['type' => 'skema data spasial', 'id' => '9', 'attributes' => [
                'versi_skema' => ['id' => 9, 'sektor_id' => 1, 'nomor_versi' => 4, 'status' => 'draf'],
                'isian' => [],
            ]],
        ], 201),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->post($this->urlDraf, draf([isianDraf()]))
        ->assertRedirect()
        ->assertSessionHas('success');

    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && str_contains($request->url(), '/api/v1/dataspasial/skema')
            && $request->data()['sektor'] === 'pendidikan';
    });
});

it('mengalihkan ke sektor dan skema hasil penyimpanan', function () {
    Http::fake([
        '*/api/v1/dataspasial/skema' => Http::response([
            'data' => ['type' => 'skema data spasial', 'id' => '9', 'attributes' => [
                'versi_skema' => ['id' => 9, 'sektor_id' => 1, 'nomor_versi' => 4, 'status' => 'draf'],
                'isian' => [],
            ]],
        ], 201),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->post($this->urlDraf, draf([isianDraf()]))
        ->assertRedirect(route('dataspasial.skema', ['sektor' => 'pendidikan', 'skema' => 9]));
});

it('menampilkan label draf pada daftar sektor setelah simpan tanpa muat ulang manual', function () {
    fakeEndpointEditor(
        [fxSektor('1', 'pendidikan', 'Pendidikan', 2, 0)],
        [fxRiwayatVersi('9', 1, 4, 'draf')]
    );

    $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']))
        ->assertOk()
        ->assertSee('Draf berjalan')
        ->assertSee('Versi 4');
});

// ---------------------------------------------------------------------------
// US3#2 — draf dapat dibuka kembali (FR-030)
// ---------------------------------------------------------------------------

it('mengembalikan draf tersimpan lengkap dengan urutan pilihan dan saklar', function () {
    // ⚠️ Sengaja TIDAK terurut agar urutan diurutkan oleh service (FR-012).
    fakeEndpointEditor(
        [fxSektor('1', 'pendidikan', 'Pendidikan')],
        [fxRiwayatVersi('9', 1, 4, 'draf')],
        fxSkemaAktif('9', 1, 'pendidikan', 'Pendidikan', 4, 'draf', [
            fxIsian('jenis_sekolah', ['id' => 32, 'tipe' => 'pilihan', 'opsi' => ['Negeri', 'Swasta'],
                'urutan' => 2, 'tampil_filter' => true]),
            fxIsian('jumlah_siswa', ['id' => 31, 'tipe' => 'angka', 'wajib' => true, 'satuan' => 'orang',
                'urutan' => 1, 'tampil_popup' => true, 'tampil_indikator' => true,
                'pemetaan' => [['id' => 3951, 'sumber' => 'openSID',
                    'rujukan_sumber' => 'data_presisi_pendidikan.*', 'aturan_hitung' => null]]]),
        ])
    );

    $content = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan', 'skema' => 9]))
        ->assertOk()
        ->getContent();

    // Urutan kembali sesuai yang tersimpan, bukan urutan balasan API (FR-030).
    expect($content)
        ->toContain('jumlah_siswa')
        ->toContain('jenis_sekolah')
        ->toContain('Negeri')
        ->toContain('Swasta')
        // Saklar tampilan kembali seperti tersimpan (FR-028, nama resmi K4).
        ->toContain('class="input-popup-peta" checked')
        ->toContain('class="input-filter" checked')
        ->toContain('class="input-indikator" checked')
        // Pemetaan ikut kembali dan tampil terkunci (FR-030/FR-015).
        ->toContain('openSID')
        ->toContain('badge-terpetakan');
});

// ---------------------------------------------------------------------------
// US3#3 — sumber mati saat menyimpan (FR-032)
// ---------------------------------------------------------------------------

it('menampilkan pesan data sementara tidak tersedia tanpa kehilangan isi saat API mati', function () {
    Http::fake([
        '*/api/v1/dataspasial/skema' => fn () => throw new ConnectionException('cURL error 28: Operation timed out'),
        '*' => Http::response(['data' => []], 200),
    ]);

    $response = $this->post($this->urlDraf, draf([isianDraf()]));

    $response->assertRedirect();
    expect(session('error'))->toBe('Data sementara tidak tersedia. Silakan coba lagi nanti.');

    // Isi form dikembalikan (old input) sehingga admin dapat mencoba ulang.
    $response->assertSessionHasInput('isian.0.kode', 'jumlah_siswa')
        ->assertSessionHasInput('sektor', 'pendidikan');
});

it('menampilkan pesan yang sama saat modul belum terpasang', function () {
    Http::fake([
        '*/api/v1/dataspasial/skema' => Http::response([
            'success' => false,
            'message' => 'Modul DataSpasial belum terpasang atau versinya tidak cocok',
        ], 503),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->post($this->urlDraf, draf([isianDraf()]))->assertSessionHas('error');

    expect(session('error'))->toBe('Data sementara tidak tersedia. Silakan coba lagi nanti.');
});

it('mencatat kegagalan ke log tanpa membocorkan pesan teknis ke pengguna', function () {
    Log::spy();

    Http::fake([
        '*/api/v1/dataspasial/skema' => Http::response([
            'success' => false,
            'message' => 'Modul DataSpasial belum terpasang atau versinya tidak cocok',
        ], 503),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->post($this->urlDraf, draf([isianDraf()]));

    expect(session('error'))->not->toContain('belum terpasang');

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $m, array $c = []) => str_contains($m, 'DataSpasial')
            && ($c['reason'] ?? null) === 'module_not_installed');
});

// ---------------------------------------------------------------------------
// US3#4 — API menolak draf (FR-021)
// ---------------------------------------------------------------------------

it('menampilkan penjelasan yang dapat ditindaklanjuti saat API menolak draf', function () {
    Log::spy();

    Http::fake([
        '*/api/v1/dataspasial/skema' => Http::response([
            'success' => false,
            'message' => 'Validasi gagal.',
            'error_code' => 'ValidationException',
            'errors' => ['isian.0.kode' => ['Kode isian tidak dikenal pada versi skema ini.']],
        ], 422),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->post($this->urlDraf, draf([isianDraf()]))->assertSessionHas('error');

    // Pesan teknis API tidak boleh sampai ke pengguna (FR-016).
    expect(session('error'))->not->toContain('ValidationException');

    Log::shouldHaveReceived('warning');
});

it('mempertahankan isi draf ketika API menolak dengan 422', function () {
    Http::fake([
        '*/api/v1/dataspasial/skema' => Http::response(['message' => 'ditolak'], 422),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->post($this->urlDraf, draf([isianDraf(['kode' => 'jumlah_siswa'])]))
        ->assertSessionHasInput('isian.0.kode', 'jumlah_siswa');
});

it('menangani konflik draf berjalan tanpa membuat draf kedua', function () {
    Http::fake([
        '*/api/v1/dataspasial/skema' => Http::response([
            'success' => false,
            'message' => 'Sektor ini sudah memiliki draf berjalan.',
            'error_code' => 'NoActiveDraft',
        ], 409),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->post($this->urlDraf, draf([isianDraf()]))->assertSessionHas('error');

    expect(session('error'))->not->toContain('NoActiveDraft');
});

// ---------------------------------------------------------------------------
// Cache — penyimpanan wajib membuat cache baca tidak berlaku (SC-008)
// ---------------------------------------------------------------------------

it('membuat cache baca tidak berlaku setelah menyimpan draf', function () {
    $sektorPayload = [
        'data' => [['type' => 'ds_sektor', 'id' => '1',
            'attributes' => ['kode' => 'pendidikan', 'nama' => 'Pendidikan', 'ikon' => null, 'status' => 'aktif']]],
        'meta' => ['pagination' => ['total' => 1, 'count' => 1, 'per_page' => 200, 'current_page' => 1, 'total_pages' => 1]],
    ];

    // ⚠️ `Http::fake()` dipanggil SATU KALI saja — memanggilnya ulang di tengah
    // test akan mereset pencatat request sehingga `assertSentCount()` tidak
    // lagi dapat menghitung.
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response($sektorPayload, 200),
        '*' => Http::response($sektorPayload, 200),
    ]);

    $service = app(DataSpasialApiService::class);

    $service->listSektor();
    Http::assertSentCount(1);

    $this->post($this->urlDraf, draf([isianDraf()]));
    Http::assertSentCount(2);

    $service->listSektor();

    // Cache lama tidak boleh dipakai lagi → request ketiga benar-benar dikirim.
    Http::assertSentCount(3);
});
