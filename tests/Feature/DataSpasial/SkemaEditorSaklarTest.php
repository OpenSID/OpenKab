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
 * US5 — Menentukan isian yang tampil di popup peta, filter, dan indikator dasbor.
 *
 * ⚠️ **UTANG KONTRAK U2** — `popup_peta`, `filter`, dan `indikator` BELUM ADA
 * pada `openapi-fondasi.yaml` v0.1.0. Fitur ini mengirimnya sebagai field asumsi
 * dan membacanya dengan default `false` bila tidak ada. Yang diuji di sini
 * adalah perilaku OpenKab, bukan-available-ness field di sisi API.
 *
 * Skenario (spec.md US5):
 *  #1/#2/#3 tiap saklar menyala dan independen (FR-026/FR-027)
 *  #4 isian dengan ketiga saklar mati tetap ada di formulir
 *  · keadaan awal nonaktif bila belum ditetapkan (FR-026)
 *  · ikut tersimpan & kembali (FR-028)
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

    $team = Team::firstOrCreate(['name' => 'admin skema us5'], ['menu' => []]);
    setPermissionsTeamId($team->id);

    $role = Role::firstOrCreate(['name' => 'admin skema us5', 'team_id' => $team->id, 'guard_name' => 'web']);
    $role->syncPermissions(array_map(
        fn (string $n) => Permission::findOrCreate($n, 'web'),
        ['dataspasial-skema-read', 'dataspasial-skema-write', 'dataspasial-skema-edit', 'dataspasial-skema-delete']
    ));

    $this->user = User::factory()->create();
    $this->user->forceFill(['name' => 'Uji Admin Skema US5'])->save();
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
function isianSaklar(array $ubah = []): array
{
    return array_merge([
        'id' => null,
        'kode' => 'jumlah_siswa',
        'label' => 'Jumlah Siswa',
        'tipe' => 'angka',
        'wajib' => false,
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
function drafSaklar(array $isian): array
{
    return ['sektor' => 'pendidikan', 'isian' => $isian];
}

// ---------------------------------------------------------------------------
// FR-026 — keadaan awal nonaktif
// ---------------------------------------------------------------------------

it('memilih keadaan awal tidak aktif bila penanda tidak dikirim API', function () {
    $editor = app(SkemaEditorService::class);

    // API tidak mengirim satu pun field asumsi (U2 belum tersedia).
    $hasil = $editor->petakanIsian(['id' => '31', 'attributes' => [
        'kode' => 'jumlah_siswa', 'label' => 'Jumlah Siswa', 'tipe' => 'angka', 'urutan' => 1,
    ]]);

    expect($hasil['popupPeta'])->toBeFalse()
        ->and($hasil['sebagaiFilter'])->toBeFalse()
        ->and($hasil['sebagaiIndikator'])->toBeFalse();
});

it('membaca penanda tampilan dari API bila sudah tersedia', function () {
    $editor = app(SkemaEditorService::class);

    $hasil = $editor->petakanIsian(['id' => '31', 'attributes' => [
        'kode' => 'luas_ha', 'label' => 'Luas', 'tipe' => 'angka', 'urutan' => 1,
        'tampil_popup' => true, 'tampil_filter' => false, 'tampil_indikator' => true,
    ]]);

    expect($hasil['popupPeta'])->toBeTrue()
        ->and($hasil['sebagaiFilter'])->toBeFalse()
        ->and($hasil['sebagaiIndikator'])->toBeTrue();
});

// ---------------------------------------------------------------------------
// FR-027 — independen satu per satu
// ---------------------------------------------------------------------------

it('mem underpinned setiap saklar secara independen', function () {
    $editor = app(SkemaEditorService::class);

    // Satu isian hanya popup peta; yang lain hanya indikator.
    $a = $editor->petakanIsian(['id' => '1', 'attributes' => ['kode' => 'a', 'tipe' => 'teks', 'urutan' => 1,
        'tampil_popup' => true, 'tampil_filter' => false, 'tampil_indikator' => false]]);
    $b = $editor->petakanIsian(['id' => '2', 'attributes' => ['kode' => 'b', 'tipe' => 'teks', 'urutan' => 2,
        'tampil_popup' => false, 'tampil_filter' => false, 'tampil_indikator' => true]]);

    expect($a['popupPeta'])->toBeTrue()
        ->and($a['sebagaiFilter'])->toBeFalse()
        ->and($a['sebagaiIndikator'])->toBeFalse()
        ->and($b['popupPeta'])->toBeFalse()
        ->and($b['sebagaiIndikator'])->toBeTrue();
});

it('mengaktifkan ketiga saklar secara bersamaan', function () {
    $editor = app(SkemaEditorService::class);

    $hasil = $editor->petakanIsian(['id' => '1', 'attributes' => ['kode' => 'a', 'tipe' => 'teks', 'urutan' => 1,
        'tampil_popup' => true, 'tampil_filter' => true, 'tampil_indikator' => true]]);

    expect($hasil['popupPeta'])->toBeTrue()
        ->and($hasil['sebagaiFilter'])->toBeTrue()
        ->and($hasil['sebagaiIndikator'])->toBeTrue();
});

// ---------------------------------------------------------------------------
// FR-028 — ikut tersimpan & kembali
// ---------------------------------------------------------------------------

it('memetakan nama internal ke nama asumsi kontrak saat menyusun draf', function () {
    $editor = app(SkemaEditorService::class);

    $payload = $editor->composeDrafPayload([
        [
            'kode' => 'luas_ha',
            'label' => 'Luas',
            'tipe' => 'angka',
            'urutan' => 1,
            // Nama INTERNAL (dari view/JS)…
            'popupPeta' => true,
            'sebagaiFilter' => false,
            'sebagaiIndikator' => true,
        ],
    ], ['sektor' => 'pendidikan']);

    // …berubah menjadi NAMA ASUMSI kontrak, dan TIDAK ADA di berkas lain (R12).
    expect($payload['isian'][0]['tampil_popup'])->toBeTrue()
        ->and($payload['isian'][0]['tampil_filter'])->toBeFalse()
        ->and($payload['isian'][0]['tampil_indikator'])->toBeTrue();
});

it('menolak nama asumsi kontrak sebagai input dan mengabaikannya diam-diam', function () {
    $editor = app(SkemaEditorService::class);

    // ⚠️ R12: `composeDrafPayload()` HANYA menerima NAMA INTERNAL. Bila ada
    // pemanggil yang mengirim nama asumsi (`popup_peta` dkk), nilainya harus
    // diabaikan — bukan diam-diam diterima, karena itu membuat asumsi
    // _resolution scattering di luar satu berkas.
    $payload = $editor->composeDrafPayload([
        ['kode' => 'a', 'label' => 'A', 'tipe' => 'teks', 'urutan' => 1,
            'tampil_popup' => true, 'sebagai_filter' => true, 'tampil_indikator' => true],
    ], ['sektor' => 'pendidikan']);

    expect($payload['isian'][0]['tampil_popup'])->toBeFalse()
        ->and($payload['isian'][0]['tampil_filter'])->toBeFalse()
        ->and($payload['isian'][0]['tampil_indikator'])->toBeFalse();
});

it('menyertakan ketiga penanda pada badan draf saat menyimpan', function () {
    Http::fake(['*' => Http::response([
        'data' => ['type' => 'ds_skema_versi', 'id' => '9', 'attributes' => ['nomor_versi' => 1]],
    ], 201)]);

    $this->post($this->urlDraf, drafSaklar([
        isianSaklar(['popupPeta' => 1, 'sebagaiFilter' => 0, 'sebagaiIndikator' => 1]),
    ]))->assertSessionHasNoErrors();

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);
        $isian = $body['isian'][0] ?? [];

        return ($isian['tampil_popup'] ?? null) === true
            && ($isian['tampil_filter'] ?? null) === false
            && ($isian['tampil_indikator'] ?? null) === true;
    });
});

// ---------------------------------------------------------------------------
// US5#4 — isian tetap ada meski ketiga saklar mati
// ---------------------------------------------------------------------------

it('tetap menyimpan isian yang tidak tampil di mana pun', function () {
    Http::fake(['*' => Http::response([
        'data' => ['type' => 'ds_skema_versi', 'id' => '9', 'attributes' => ['nomor_versi' => 1]],
    ], 201)]);

    $this->post($this->urlDraf, drafSaklar([
        isianSaklar(['popupPeta' => 0, 'sebagaiFilter' => 0, 'sebagaiIndikator' => 0]),
    ]))->assertSessionHasNoErrors();

    Http::assertSent(function ($request) {
        $body = json_decode($request->body(), true);

        // Isian tetap terkirim lengkap; hanya penanda tampilan yang mati.
        return ($body['isian'][0]['kode'] ?? null) === 'jumlah_siswa'
            && ($body['isian'][0]['tampil_popup'] ?? null) === false;
    });
});

// ---------------------------------------------------------------------------
// Saklar tampil pada tabel isian
// ---------------------------------------------------------------------------

it('merender ketiga saklar pada tabel isian sesuai keadaan tersimpan', function () {
    fakeEndpointEditor(
        [fxSektor('1', 'pendidikan', 'Pendidikan')],
        [fxRiwayatVersi('9', 1, 1, 'draf')],
        fxSkemaAktif('9', 1, 'pendidikan', 'Pendidikan', 1, 'draf', [
            fxIsian('jumlah_siswa', ['id' => 31, 'urutan' => 1, 'tampil_popup' => true, 'tampil_indikator' => true]),
            fxIsian('luas_ha', ['id' => 32, 'urutan' => 2]),
        ])
    );

    $content = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan', 'skema' => 9]))
        ->assertOk()
        ->getContent();

    expect($content)
        ->toContain('title="Tampil di popup peta"')
        ->toContain('title="Tersedia sebagai filter"')
        ->toContain('title="Dipakai sebagai indikator dasbor"')
        ->toContain('class="input-popup-peta" checked')
        ->toContain('class="input-indikator" checked')
        ->toContain('class="input-filter"');
});

it('menonaktifkan ketiga saklar pada skema yang sudah terbit', function () {
    fakeEndpointEditor(
        [fxSektor('1', 'pendidikan', 'Pendidikan')],
        [fxRiwayatVersi('5', 1, 2, 'terbit')],
        fxSkemaAktif('5', 1, 'pendidikan', 'Pendidikan', 2, 'terbit', [
            fxIsian('jumlah_siswa', ['id' => 31]),
        ])
    );

    $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan', 'skema' => 5]))
        ->assertOk()
        ->assertSee('Skema versi terbit hanya dapat dibaca')
        // Badge tampil read-only: tanpa tombol pengubah (FR-008a).
        ->assertDontSee('btn-pilih-sumber-baris');
});
