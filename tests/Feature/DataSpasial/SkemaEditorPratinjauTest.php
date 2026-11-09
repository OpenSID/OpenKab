<?php

declare(strict_types=1);

use App\Enums\TipeIsian;
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
 * US6 — Pratinjau formulir desa.
 *
 * Pratinjau dibangun dari state klien yang SAMA dengan form editor
 * (`renderPratinjau(stateIsian)` di `schema-editor.js`, research.md R7).
 * Yang diuji di sini adalah **read model pratinjau** yang/view Newman memakai
 * sebagai sumber — perilaku render di sisi klien diverifikasi manual lewat
 * `quickstart.md` §2 (browser smoke dibatalkan, lihat T033).
 *
 * Yang dijaga test ini:
 *  - urutan pratinjau mengikuti `urutan` tersimpan (FR-034);
 *  - isian terpetakan adalah TERKUNCI + punya keterangan sumber (FR-015);
 *  - nilai pemetaan kosong tetap terkunci (FR-037);
 *  - parity dengan OpenSID diikat lewat fixture kontrak yang sama (SC-006).
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

    $team = Team::firstOrCreate(['name' => 'admin skema us6'], ['menu' => []]);
    setPermissionsTeamId($team->id);

    $role = Role::firstOrCreate(['name' => 'admin skema us6', 'team_id' => $team->id, 'guard_name' => 'web']);
    $role->syncPermissions(array_map(
        fn (string $n) => Permission::findOrCreate($n, 'web'),
        ['dataspasial-skema-read', 'dataspasial-skema-write', 'dataspasial-skema-edit', 'dataspasial-skema-delete']
    ));

    $this->user = User::factory()->create();
    $this->user->forceFill(['name' => 'Uji Admin Skema US6'])->save();
    $this->user->teams()->attach($team->id, ['id_user' => $this->user->id, 'id_team' => $team->id]);
    $this->user->unsetRelation('team')->load('team');
    $this->user->assignRole($role);

    test()->actingAs($this->user, 'web');
});

/**
 * @param  array<string, mixed>  $ubah
 * @return array<string, mixed>
 */
function isianPratinjau(array $ubah = []): array
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
 * Fixture isian yang disalin dari `contracts/skema-editor-api.md` §2.2.
 *
 * Test parity (T068) memakai SUSUNAN YANG SAMA dengan contoh kontrak agar
 * perubahan pada read model langsung terlihat di diff.
 *
 * @return array<int, array<string, mixed>>
 */
function fixtureIsianKontrak(): array
{
    return [
        isianPratinjau(['id' => 31, 'kode' => 'jumlah_siswa', 'label' => 'Jumlah Siswa',
            'tipe' => 'angka', 'wajib' => true, 'satuan' => 'orang', 'urutan' => 1,
            'popupPeta' => true, 'sebagaiIndikator' => true]),
        isianPratinjau(['id' => 32, 'kode' => 'jenis_sekolah', 'label' => 'Jenis Sekolah',
            'tipe' => 'pilihan', 'wajib' => false, 'satuan' => null, 'urutan' => 2,
            'opsi' => ['Negeri', 'Swasta'], 'sebagaiFilter' => true]),
        isianPratinjau(['id' => 33, 'kode' => 'luas_ha', 'label' => 'Luas (ha)',
            'tipe' => 'angka', 'wajib' => false, 'satuan' => 'ha', 'urutan' => 3]),
        isianPratinjau(['id' => 34, 'kode' => 'tanggal_pendataan', 'label' => 'Tanggal Pendataan',
            'tipe' => 'tanggal', 'wajib' => false, 'satuan' => null, 'urutan' => 4]),
        isianPratinjau(['id' => 35, 'kode' => 'aktif', 'label' => 'Aktif',
            'tipe' => 'ya_tidak', 'wajib' => false, 'satuan' => null, 'urutan' => 5]),
    ];
}

// ---------------------------------------------------------------------------
// FR-034 — pratinjau mencerminkan susunan isian
// ---------------------------------------------------------------------------

it('mempertahankan urutan isian pada pratinjau sesuai urutan tersimpan', function () {
    $editor = app(SkemaEditorService::class);

    // Sengaja terbalik agar urutan TIDAK ikut urutan respons API.
    $acak = array_reverse(fixtureIsianKontrak());

    $hasil = $editor->petakanDaftarIsian(array_map(
        fn (array $b): array => ['id' => (string) $b['id'], 'attributes' => $b],
        $acak
    ));

    expect(array_column($hasil, 'kode'))->toBe([
        'jumlah_siswa', 'jenis_sekolah', 'luas_ha', 'tanggal_pendataan', 'aktif',
    ])->and(array_column($hasil, 'urutan'))->toBe([1, 2, 3, 4, 5]);
});

it('menyatakan isian wajib dan satuan pada pratinjau', function () {
    $editor = app(SkemaEditorService::class);

    $hasil = $editor->petakanDaftarIsian(array_map(
        fn (array $b): array => ['id' => (string) $b['id'], 'attributes' => $b],
        fixtureIsianKontrak()
    ));

    $wajib = array_values(array_filter($hasil, fn (array $b): bool => $b['wajib']));

    expect($wajib)->toHaveCount(1)
        ->and($wajib[0]['kode'])->toBe('jumlah_siswa')
        ->and($wajib[0]['satuan'])->toBe('orang');

    // Satuan hanya relevan pada tipe angka (FR-010).
    $luas = array_values(array_filter($hasil, fn (array $b): bool => $b['kode'] === 'luas_ha'))[0];

    expect(TipeIsian::fromValue($luas['tipe'])->memakaiSatuan())->toBeTrue();

    $tanggal = array_values(array_filter($hasil, fn (array $b): bool => $b['kode'] === 'tanggal_pendataan'))[0];

    expect(TipeIsian::fromValue($tanggal['tipe'])->memakaiSatuan())->toBeFalse()
        ->and($tanggal['satuan'])->toBeNull();
});

// ---------------------------------------------------------------------------
// FR-015 — isian terpetakan terkunci
// ---------------------------------------------------------------------------

it('menandai isian terpetakan agar tampil terkunci di pratinjau', function () {
    $editor = app(SkemaEditorService::class);

    // ⚠️ VERIFIKASI LANGSUNG: pemetaan TERTANAM di isian; `sumber` = ENUM API.
    $hasil = $editor->petakanDaftarIsian([
        ['id' => '31', 'attributes' => fxIsian('jumlah_siswa', ['id' => 31, 'urutan' => 1, 'pemetaan' => [
            ['id' => 3951, 'sumber' => 'dataPresisi', 'rujukan_sumber' => 'x', 'aturan_hitung' => null],
        ]])],
        ['id' => '32', 'attributes' => fxIsian('luas_ha', ['id' => 32, 'urutan' => 2])],
    ]);

    expect($hasil[0]['sumber'])->toBe('dataPresisi')
        ->and($hasil[0]['rujukan'])->toBe('x')
        // Sisa isian tetap manual → tidak terkunci.
        ->and($hasil[1]['sumber'])->toBe('manual');
});

it('menyimpan keterangan sumber yang terbaca pengguna', function () {
    $editor = app(SkemaEditorService::class);

    $hasil = $editor->petakanDaftarIsian([
        ['id' => '31', 'attributes' => fxIsian('luas_ha', ['id' => 31, 'pemetaan' => [
            ['id' => 1, 'sumber' => 'openSID', 'rujukan_sumber' => 'luas_wilayah.*', 'aturan_hitung' => null],
        ]])],
    ]);

    // `sumber` adalah teks yang ditampilkan pada badge & pratinjau terkunci.
    expect($hasil[0]['sumber'])->toBeString()->toBe('openSID')
        ->and($hasil[0]['rujukan'])->toBe('luas_wilayah.*');
});

it('menandai nilai pemetaan kosong agar pratinjau memberi keterangan belum tersedia (FR-037)', function () {
    $editor = app(SkemaEditorService::class);

    // Pemetaan ada tapi rujukannya kosong → nilai belum tersedia dari sumber.
    $hasil = $editor->petakanDaftarIsian([
        ['id' => '31', 'attributes' => fxIsian('luas_ha', ['id' => 31, 'pemetaan' => [
            ['id' => 1, 'sumber' => 'openSID', 'rujukan_sumber' => null, 'aturan_hitung' => null],
        ]])],
    ]);

    expect($hasil[0]['sumber'])->toBe('openSID')
        ->and($hasil[0]['rujukan'])->toBeNull();
});

// ---------------------------------------------------------------------------
// T068 — PARITAS DENGAN OPENSID (SC-006)
// Fixture dari `contracts/skema-editor-api.md` §2.2 — sumber kebenaran tunggal.
// ---------------------------------------------------------------------------

it('mempertahankan paritas atribut formulir dengan OpenSID untuk skema yang sama', function () {
    $editor = app(SkemaEditorService::class);

    $hasil = $editor->petakanDaftarIsian(array_map(
        fn (array $b): array => ['id' => (string) $b['id'], 'attributes' => $b],
        fixtureIsianKontrak()
    ));

    // Kontrak listing yang harus dipertahankan persis oleh OpenKab:
    // urutan · kode · label · tipe · wajib · satuan · opsi
    $hasilFormulir = array_map(fn (array $b): array => [
        $b['urutan'],
        $b['kode'],
        $b['label'],
        $b['tipe'],
        $b['wajib'],
        $b['satuan'],
        $b['opsi'],
    ], $hasil);

    expect($hasilFormulir)->toBe([
        [1, 'jumlah_siswa', 'Jumlah Siswa', 'angka', true, 'orang', []],
        [2, 'jenis_sekolah', 'Jenis Sekolah', 'pilihan', false, null, ['Negeri', 'Swasta']],
        [3, 'luas_ha', 'Luas (ha)', 'angka', false, 'ha', []],
        [4, 'tanggal_pendataan', 'Tanggal Pendataan', 'tanggal', false, null, []],
        [5, 'aktif', 'Aktif', 'ya_tidak', false, null, []],
    ]);
});

it('menjaga agar penyimpangan paritas terdeteksi saat data berubah', function () {
    $editor = app(SkemaEditorService::class);

    $hasil = $editor->petakanDaftarIsian(array_map(
        fn (array $b): array => ['id' => (string) $b['id'], 'attributes' => $b],
        fixtureIsianKontrak()
    ));

    // Guard: kalau ada isian yang label/satuan-nya berubah diam-diam, test ini
    // harus gagal — itulah mekanisme deteksi penyimpangan paritas (SC-006).
    $paritas = array_map(fn (array $b): string => $b['label'].'|'.$b['satuan'], $hasil);

    expect($paritas)->toBe([
        'Jumlah Siswa|orang',
        'Jenis Sekolah|',
        'Luas (ha)|ha',
        'Tanggal Pendataan|',
        'Aktif|',
    ]);
});

// ---------------------------------------------------------------------------
// T070 — Panel pratinjau dirender pada halaman
// ---------------------------------------------------------------------------

it('merender panel pratinjau beserta wadah yang diisi JS', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response([
            'data' => [['type' => 'ds_sektor', 'id' => '1',
                'attributes' => ['kode' => 'pendidikan', 'nama' => 'Pendidikan', 'ikon' => null, 'status' => 'aktif']]],
            'meta' => ['pagination' => ['total' => 1, 'count' => 1, 'per_page' => 200, 'current_page' => 1, 'total_pages' => 1]],
        ], 200),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan', 'skema' => 9]))
        ->assertOk()
        ->assertSee('Pratinjau Formulir Desa')
        ->assertSee('wadah-pratinjau', false)
        // Skrip editor memuat fungsi render pratinjau (R7).
        ->assertSee('js/dataspasial/schema-editor.js', false);
});

it('menampilkan penjelasan pada pratinjau ketika sektor belum dipilih', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response([
            'data' => [['type' => 'ds_sektor', 'id' => '1',
                'attributes' => ['kode' => 'pendidikan', 'nama' => 'Pendidikan', 'ikon' => null, 'status' => 'aktif']]],
            'meta' => ['pagination' => ['total' => 1, 'count' => 1, 'per_page' => 200, 'current_page' => 1, 'total_pages' => 1]],
        ], 200),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->get(route('dataspasial.skema'))
        ->assertOk()
        ->assertSee('Pratinjau formulir akan muncul setelah sektor dipilih');
});

// ---------------------------------------------------------------------------
// FR-015/Q16 — perbedaan perlakuan isian `manual` vs isian terpetakan
//
// ⚠️ `renderPratinjau()` berjalan di sisi klien (tidak diuji di sini). Yang
// dikunci test ini adalah KONDISI yang dibaca render tersebut:
// `terkunci = sumber && sumber !== 'manual'`. Bila aturan ini berubah, isian
// manual ikut berubah dari "bisa diisi" menjadi "terkunci" tanpa terdeteksi.
// ---------------------------------------------------------------------------

it('membuat isian manual terbaca sebagai tidak terkunci di pratinjau', function () {
    $hasil = app(SkemaEditorService::class)->petakanDaftarIsian([
        ['id' => '31', 'attributes' => fxIsian('jumlah_siswa', ['id' => 31, 'urutan' => 1])],
    ]);

    // Tanpa pemetaan → `sumber` = 'manual' → renderMenampilkan input YANG BISA
    // DIISI sebagai simulasi operator (Q16).
    expect($hasil[0]['sumber'])->toBe('manual');

    // Meniru kondisi yang dipakai `renderPratinjau()` di schema-editor.js.
    $terkunci = (bool) ($hasil[0]['sumber'] && $hasil[0]['sumber'] !== 'manual');
    expect($terkunci)->toBeFalse();
});

it('membuat isian terpetakan terbaca sebagai terkunci di pratinjau', function (string $sumber) {
    $hasil = app(SkemaEditorService::class)->petakanDaftarIsian([
        ['id' => '31', 'attributes' => fxIsian('jumlah_siswa', ['id' => 31, 'urutan' => 1, 'pemetaan' => [
            ['id' => 1, 'sumber' => $sumber, 'rujukan_sumber' => 'jumlah_siswa', 'aturan_hitung' => null],
        ]])],
    ]);

    $terkunci = (bool) ($hasil[0]['sumber'] && $hasil[0]['sumber'] !== 'manual');

    expect($terkunci)->toBeTrue()
        ->and($hasil[0]['sumber'])->toBe($sumber);
})->with(['openSID', 'dataPresisi', 'impor']);
