<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(DatabaseTransactions::class);

/**
 * FR-017/FR-018/FR-019/FR-020: validasi kode isian & bentuk draf.
 *
 * Aturan yang diuji (spec.md):
 * - Kode isian WAJIB `snake_case` (huruf kecil + angka + garis bawah).
 * - Kode bentrok ditolak DI DALAM satu draf (FR-018).
 * - Kode sama pada versi berbeda TIDAK dianggap bentrok (FR-018).
 * - Tipe `pilihan` tanpa opsi ditolak (FR-019).
 * - Sektor dengan nol isian ditolak (FR-019).
 *
 * Semua penolakan terjadi SEBELUM request ke API (FR-020) — dibuktikan dengan
 * `Http::assertNothingSent()`.
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

    // Admin Skema dengan izin tulis. Izin `dataspasial-skema-*` sudah tersedia
    // dari KAB-01 (T001) — test ini TIDAK membuat permission baru.
    actingAsAdminSkema();

    $this->routeDraf = route('dataspasial.skema.draf.store');
});

/**
 * Bikin pengguna yang/group punya izin tulis skema (FR-001).
 *
 * Pola mengikuti `DataSpasialMenuAccessTest` (spatie teams).
 */
function actingAsAdminSkema(string $teamName = 'admin skema uji'): User
{
    $team = Team::firstOrCreate(['name' => $teamName], ['menu' => []]);

    setPermissionsTeamId($team->id);

    $role = Role::firstOrCreate([
        'name' => $teamName,
        'team_id' => $team->id,
        'guard_name' => 'web',
    ]);

    $role->syncPermissions(array_map(
        fn (string $nama) => Permission::findOrCreate($nama, 'web'),
        ['dataspasial-skema-read', 'dataspasial-skema-write', 'dataspasial-skema-edit', 'dataspasial-skema-delete']
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
 * Bentuk isian yang valid; dapat ditimpa per kasus.
 *
 * @param  array<string, mixed>  $ubah
 * @return array<string, mixed>
 */
function isianValid(array $ubah = []): array
{
    return array_merge([
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
function drafPayload(array $isian, array $tambah = []): array
{
    return array_merge([
        'sektor' => 'pendidikan',
        'catatan_perubahan' => 'Draf uji.',
    ], $tambah, ['isian' => $isian]);
}

// ---------------------------------------------------------------------------
// FR-017/FR-018 — `kode` DITURUNKAN dari `label` (Q14), unik per versi (Q17)
//
// ⚠️ Admin TIDAK lagi mengetik `kode`. Yang diuji di sini adalah bentuk dari
// nilai yang sudah dibangkitkan klien, plus keunikan `label`/`kode` di dalam
// SATU versi yang dikirim.
// ---------------------------------------------------------------------------

// ⚠️ Q14 — yang diuji adalah NILAI TURUNAN (hasil slum-bangian di sisi klien),
// karena OpenKab tidak lagi menerima kode yang diketik manual.
it('menolak kode turunan yang tidak sah sebelum mengirim ke API', function (string $kode) {
    Http::fake();

    $this->post($this->routeDraf, drafPayload([isianValid(['kode' => $kode])]))
        ->assertSessionHasErrors('isian.0.kode');

    Http::assertNothingSent();
})->with([
    'kosong dari label kosong' => '',
    'kosong dari label simbol' => '',
    'dimulai angka dari label "2Jumlah"' => '2jumlah',
    'mengandung spasi' => 'jumlah siswa',
    'berkas dengan tanda hubung' => 'jumlah-siswa',
]);

it('menerima kode turunan dari label yang sah', function (string $label, string $kode) {
    Http::fake(['*' => Http::response([
        'data' => ['type' => 'ds_skema_versi', 'id' => '5', 'attributes' => ['nomor_versi' => 1]],
    ], 201)]);

    $this->post($this->routeDraf, drafPayload([
        isianValid(['label' => $label, 'kode' => $kode]),
    ]))->assertSessionHasNoErrors();

    Http::assertSent(fn ($request) => str_contains($request->body(), $kode));
})->with([
    ['Jumlah Siswa', 'jumlah_siswa'],
    ['Luas (Ha)', 'luas_ha'],
    ['Jumlah', 'jumlah'],
    ['Village Code 2', 'village_code_2'],
]);

// ---------------------------------------------------------------------------
// FR-018/Q17 — keunikan label & kode DI DALAM satu versi
// ---------------------------------------------------------------------------

it('menolak dua isian berlabel sama dalam satu versi sebelum mengirim ke API', function () {
    Http::fake();

    $this->post($this->routeDraf, drafPayload([
        isianValid(['label' => 'Jumlah Siswa', 'urutan' => 1]),
        isianValid(['label' => 'Jumlah Siswa', 'urutan' => 2]),
    ]))->assertSessionHasErrors('isian.1.label');

    Http::assertNothingSent();
});

it('menolak dua isian berlabel berbeda yang dipadatkan menjadi kode sama', function () {
    Http::fake();

    $this->post($this->routeDraf, drafPayload([
        isianValid(['label' => 'Jumlah Siswa', 'kode' => 'jumlah_siswa', 'urutan' => 1]),
        isianValid(['label' => 'Jumlah-Siswa', 'kode' => 'jumlah_siswa', 'urutan' => 2]),
    ]))->assertSessionHasErrors();

    Http::assertNothingSent();
});

it('menerima label dan kode berbeda pada beberapa isian dalam satu versi', function () {
    Http::fake(['*' => Http::response([
        'data' => ['type' => 'ds_skema_versi', 'id' => '5', 'attributes' => ['nomor_versi' => 1]],
    ], 201)]);

    $this->post($this->routeDraf, drafPayload([
        isianValid(['label' => 'Jumlah Siswa', 'kode' => 'jumlah_siswa', 'urutan' => 1]),
        isianValid(['label' => 'Jumlah Guru', 'kode' => 'jumlah_guru', 'urutan' => 2]),
        isianValid(['label' => 'Luas Ha', 'kode' => 'luas_ha', 'urutan' => 3]),
    ]))->assertSessionHasNoErrors();
});

// ⚠️ Q17 — keunikan berlaku PER VERSI. Label yang sama pada versi berbeda
// **tidak** ditolak, karena satu request = satu versi dan draf berikutnya
// disalin dari versi sebelumnya.
it('tidak menolak label yang sama pada versi berbeda karena keunikan per versi', function () {
    Http::fake(['*' => Http::response([
        'data' => ['type' => 'ds_skema_versi', 'id' => '5', 'attributes' => ['nomor_versi' => 3]],
    ], 201)]);

    $response = $this->post($this->routeDraf, drafPayload([
        isianValid(['label' => 'Jumlah Siswa', 'kode' => 'jumlah_siswa', 'urutan' => 1]),
    ], ['nomor_versi' => 3]));

    $response->assertSessionHasNoErrors();
    $response->assertSessionHas('success');
});

// ---------------------------------------------------------------------------
// FR-018 — keunikan kode di dalam satu draf
// ---------------------------------------------------------------------------

it('menolak kode isian bentrok di dalam satu draf sebelum mengirim ke API', function () {
    Http::fake();

    $this->post($this->routeDraf, drafPayload([
        isianValid(['kode' => 'jumlah_siswa', 'urutan' => 1]),
        isianValid(['kode' => 'jumlah_siswa', 'label' => 'Jumlah Siswa Putri', 'urutan' => 2]),
    ]))->assertSessionHasErrors('isian.1.kode');

    Http::assertNothingSent();
});

// ---------------------------------------------------------------------------
// FR-019 — isian wajib tipe pilihan & sektor tanpa isian
// ---------------------------------------------------------------------------

it('menolak isian wajib tipe pilihan tanpa butir pilihan', function () {
    Http::fake();

    $this->post($this->routeDraf, drafPayload([
        isianValid(['kode' => 'jenis_sekolah', 'tipe' => 'pilihan', 'opsi' => [], 'wajib' => true]),
    ]))->assertSessionHasErrors('isian.0.opsi');

    Http::assertNothingSent();
});

it('menolak draf yang menyebabkan sektor memiliki nol isian', function () {
    Http::fake();

    $this->post($this->routeDraf, drafPayload([]))
        ->assertSessionHasErrors('isian');

    Http::assertNothingSent();
});

// ---------------------------------------------------------------------------
// Atribut wajib lain
// ---------------------------------------------------------------------------

it('menolak isian tanpa kode, tanpa label, dan tanpa tipe', function () {
    Http::fake();

    $this->post($this->routeDraf, drafPayload([
        ['urutan' => 1],
    ]))->assertSessionHasErrors(['isian.0.kode', 'isian.0.label', 'isian.0.tipe']);

    Http::assertNothingSent();
});

it('menolak tipe isian yang tidak dikenal', function () {
    Http::fake();

    $this->post($this->routeDraf, drafPayload([
        isianValid(['tipe' => 'warna']),
    ]))->assertSessionHasErrors('isian.0.tipe');

    Http::assertNothingSent();
});

it('menolak draf tanpa identitas sektor', function () {
    Http::fake();

    $this->post($this->routeDraf, drafPayload([isianValid()], ['sektor' => '']))
        ->assertSessionHasErrors('sektor');

    Http::assertNothingSent();
});

it('menerima semua tipe isian yang didukung', function (string $tipe) {
    Http::fake(['*' => Http::response([
        'data' => ['type' => 'ds_skema_versi', 'id' => '5', 'attributes' => ['nomor_versi' => 1]],
    ], 201)]);

    $this->post($this->routeDraf, drafPayload([
        isianValid(['kode' => 'nilai_satu', 'tipe' => $tipe, 'opsi' => $tipe === 'pilihan' ? ['A'] : null]),
    ]))->assertSessionHasNoErrors();
})->with(['angka', 'teks', 'pilihan', 'ya_tidak', 'tanggal']);

// ---------------------------------------------------------------------------
// FR-020 — tidak ada request terkirim saat validasi gagal
// ---------------------------------------------------------------------------

it('tidak mengirim request apa pun ke API ketika draf ditolak validasi', function () {
    Http::fake();

    // ⚠️ Q14 — `kode` tidak lagi diketik admin. Yang ditolak di sini adalah
    // kode TURUNAN yang tidak sah bentuk, yaitu hasil *slug* dari label
    // yang tidak menghasilkan slug valid.
    $this->post($this->routeDraf, drafPayload([
        isianValid(['label' => '!!!', 'kode' => '']),
    ]));

    Http::assertNothingSent();
});
