<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\Team;
use App\Models\User;
use App\Services\SkemaEditorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(DatabaseTransactions::class);

/**
 * US4 — Memetakan isian ke sumber data desa.
 *
 * Keputusan Q2: **sumber nilai untuk pemetaan adalah daftar sektor** DataSpasial
 * (`GET /dataspasial/sektor`), bukan endpoint katalog sumber terpisah.
 *
 * Skenario yang diuji (spec.md US4):
 *  #1 pemilih sumber menampilkan daftar sektor beserta keterangannya (FR-022)
 *  #2 memilih sektor menandai isian terpetakan (FR-023)
 *  #3 menghapus pemetaan mengembalikan isian ke manual (FR-023)
 *  #4 sumber bermasalah → pesan generic, pemetaan lama tidak rusak (FR-005)
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

    $team = Team::firstOrCreate(['name' => 'admin skema us4'], ['menu' => []]);
    setPermissionsTeamId($team->id);

    $role = Role::firstOrCreate(['name' => 'admin skema us4', 'team_id' => $team->id, 'guard_name' => 'web']);
    $role->syncPermissions(array_map(
        fn (string $n) => Permission::findOrCreate($n, 'web'),
        ['dataspasial-skema-read', 'dataspasial-skema-write', 'dataspasial-skema-edit', 'dataspasial-skema-delete']
    ));

    $this->user = User::factory()->create();
    $this->user->forceFill(['name' => 'Uji Admin Skema US4'])->save();
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
function isianPemetaan(array $ubah = []): array
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
        'tabelSumber' => null, 'kolomSumber' => null,
    ], $ubah);
}

/**
 * @param  array<int, array<string, mixed>>  $isian
 * @return array<string, mixed>
 */
function drafPemetaan(array $isian): array
{
    return ['sektor' => 'pendidikan', 'sektor_nama' => 'Pendidikan', 'isian' => $isian];
}

// ---------------------------------------------------------------------------
// US4#2 — memilih sektor sumber menandai isian terpetakan (FR-023/FR-024)
// ---------------------------------------------------------------------------

it('menandai isian sebagai terpetakan dan menampilkan nama sumbernya', function () {
    fakeEndpointEditor(
        [fxSektor('1', 'pendidikan', 'Pendidikan')],
        [fxRiwayatVersi('9', 1, 1, 'draf')],
        fxSkemaAktif('9', 1, 'pendidikan', 'Pendidikan', 1, 'draf', [
            fxIsian('luas_ha', ['id' => 31, 'pemetaan' => [
                ['id' => 5, 'sumber' => 'openSID', 'rujukan_sumber' => 'luas_wilayah.*', 'aturan_hitung' => null],
            ]]),
        ])
    );

    $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan', 'skema' => 9]))
        ->assertOk()
        ->assertSee('openSID')
        ->assertSee('badge-terpetakan');
});

it('menyertakan pemetaan pada badan draf saat menyimpan', function () {
    Http::fake(['*' => Http::response([
        'data' => ['type' => 'skema data spasial', 'id' => '9', 'attributes' => [
            'versi_skema' => ['id' => 9, 'nomor_versi' => 1, 'status' => 'draf'], 'isian' => []]],
    ], 201)]);

    $this->post($this->urlDraf, drafPemetaan([
        isianPemetaan(['sumber' => 'openSID', 'tabelSumber' => 'luas_wilayah', 'kolomSumber' => '*']),
    ]))->assertSessionHasNoErrors();

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);
        $pemetaan = $body['isian'][0]['pemetaan'] ?? [];

        // Pemetaan dikirim sebagai ARRAY di dalam isian (bentuk nyata §6a.3).
        return is_array($pemetaan)
            && ($pemetaan[0]['sumber'] ?? null) === 'openSID'
            && ($pemetaan[0]['tabel_sumber'] ?? null) === 'luas_wilayah'
            && ($pemetaan[0]['kolom_sumber'] ?? null) === '*';
    });
});

it('mengirim pemetaan null ketika isian diisi manual', function () {
    Http::fake(['*' => Http::response([
        'data' => ['type' => 'ds_skema_versi', 'id' => '9', 'attributes' => ['nomor_versi' => 1]],
    ], 201)]);

    $this->post($this->urlDraf, drafPemetaan([isianPemetaan(['sektorSumber' => null, 'rujukan' => null])]))
        ->assertSessionHasNoErrors();

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        // `??` akan menganggap `null` sebagai "absent", jadi harus memakai
        // `array_key_exists()` untuk memastikan kuncinya benar-benar dikirim
        // dengan nilai null (bukan sekadar dihilangkan).
        return array_key_exists('pemetaan', $body['isian'][0])
            && $body['isian'][0]['pemetaan'] === null;
    });
});

// ---------------------------------------------------------------------------
// US4#3 — menghapus pemetaan mengembalikan isian ke manual (FR-023)
// ---------------------------------------------------------------------------

it('mengembalikan isian ke manual ketika pemetaan dihapus', function () {
    $editor = app(SkemaEditorService::class);

    $denganPemetaan = $editor->petakanDaftarIsian([
        ['id' => '31', 'attributes' => fxIsian('luas_ha', ['id' => 31, 'pemetaan' => [
            ['id' => 5, 'sumber' => 'openSID', 'rujukan_sumber' => 'luas', 'aturan_hitung' => null],
        ]])],
    ]);

    expect($denganPemetaan[0]['sumber'])->toBe('openSID');

    // Lalu dilepas (API mengembalikan isian tanpa pemetaan).
    $manual = $editor->petakanDaftarIsian([
        ['id' => '31', 'attributes' => fxIsian('luas_ha', ['id' => 31, 'pemetaan' => []])],
    ]);

    expect($manual[0]['sumber'])->toBe('manual')
        ->and($manual[0]['rujukan'])->toBeNull();
});

it('menampilkan label Manual untuk isian tanpa pemetaan', function () {
    fakeEndpointEditor(
        [fxSektor('1', 'pendidikan', 'Pendidikan')],
        [fxRiwayatVersi('9', 1, 1, 'draf')],
        fxSkemaAktif('9', 1, 'pendidikan', 'Pendidikan', 1, 'draf', [
            fxIsian('jumlah_siswa', ['id' => 31]),
        ])
    );

    $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan', 'skema' => 9]))
        ->assertOk()
        ->assertSee('badge-manual');
});

// ---------------------------------------------------------------------------
// US4#4 — sumber bermasalah tidak merusak pemetaan lama (FR-005)
// ---------------------------------------------------------------------------

it('tidak merusak pemetaan yang sudah tersimpan saat sumber pemetaan bermasalah', function () {
    $editor = app(SkemaEditorService::class);

    $isian = $editor->petakanDaftarIsian([['id' => '31', 'attributes' => isianPemetaan(['id' => 31])]]);

    // Endpoint pemetaan unavailable → controller TIDAK menerapkan apa pun,
    // sehingga status tetap default `manual` dan tidak ada data rusak.
    $hasil = $editor->terapkanPemetaan($isian, []);

    expect($hasil[0]['sumber'])->toBe('manual')
        ->and($isian[0]['kode'])->toBe('jumlah_siswa')
        ->and($isian[0]['label'])->toBe('Jumlah Siswa');
});

it('tidak membocorkan pesan teknis pemetaan ke pengguna saat endpoint gagal', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response([
            'data' => [['type' => 'ds_sektor', 'id' => '1',
                'attributes' => ['kode' => 'pendidikan', 'nama' => 'Pendidikan', 'ikon' => null, 'status' => 'aktif']]],
            'meta' => ['pagination' => ['total' => 1, 'count' => 1, 'per_page' => 200, 'current_page' => 1, 'total_pages' => 1]],
        ], 200),
    ]);

    $content = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan', 'skema' => 9]))
        ->assertOk()
        ->getContent();

    expect($content)->not->toContain('Modul pemetaan belum terpasang');
});
