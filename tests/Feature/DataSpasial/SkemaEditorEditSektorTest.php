<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Services\DataSpasialApiService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(DatabaseTransactions::class);

/**
 * US7 — Mengubah sektor yang sudah ada (FR-007d-4 … FR-007d-9).
 *
 * Skenario yang diuji (spec.md US7):
 *  #1 form edit memuat nama & ikon terisi, kode BACA-SAJA (FR-007d-5)
 *  #2 simpan sukses → nama terbaru tampil tanpa reload (FR-007d-9)
 *  #3 nama kosong ditolak (FR-007d-6)
 *  #4 nama bentrok dengan sektor lain ditolak
 *  #5 `404`/`405` → "data sementara tidak tersedia", daftar sektor tidak berubah (FR-007d-7)
 *
 * ⚠️ **UTANG KONTRAK U5** — `PUT /dataspasial/sektor/{id}` belum ada di kontrak
 * v0.1.0 maupun di API hidup. Bentuk asumsinya ada di `contracts/openapi-fragment.yaml`.
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

    $this->adminSkema = makeRole('admin skema us7', [
        'dataspasial-skema-read',
        'dataspasial-skema-write',
        'dataspasial-skema-edit',
        'dataspasial-skema-delete',
    ]);

    test()->actingAs($this->adminSkema, 'web');
});

/**
 * Pasang stub HTTP untuk halaman editor **beserta** respons `PUT sektor/{id}`.
 *
 * ⚠️ Dua hal yang menyesatkan pada `Http::fake()`:
 *  1. Pola **FIFO** — pola yang terdaftar lebih dulu selalu menang, sehingga
 *     wildcard `*` menutupi semua stub setelahnya.
 *  2. Awalan `PUT `/`POST ` BUKAN filter method — awalan itu ikut diperlakukan
 *     sebagai glob dan membuat URL tidak akan pernah cocok. Karena itu
 *     pemisahan GET/PUT dilakukan di dalam closure.
 *
 * @param  array<int, array<string, mixed>>  $sektor
 */
function fakeEditor(?int $putStatus = null, ?array $sektor = null): void
{
    $sektor ??= [fxSektor('101', 'pendidikan', 'Pendidikan')];

    Http::fake([
        '*/api/v1/config/*' => Http::response(fakeConfigEndpoint(), 200),
        '*/api/v1/dataspasial/sektor*' => function (Request $request) use ($putStatus, $sektor) {
            if ($request->method() === 'PUT') {
                if ($putStatus === 422) {
                    return Http::response([
                        'message' => 'Nama sektor sudah digunakan.',
                        'errors' => ['nama' => ['Nama sektor sudah digunakan.']],
                    ], 422);
                }

                return $putStatus === null
                    ? Http::response(responsUbahSektor(), 200)
                    : Http::response(['message' => 'Gagal'], $putStatus);
            }

            return Http::response(fakeKoleksi($sektor), 200);
        },
        '*/api/v1/dataspasial/skema/riwayat*' => Http::response(
            fakeKoleksi([fxRiwayatVersi('901', 101, 1, 'draf')]), 200
        ),
        '*/api/v1/dataspasial/skema?*' => Http::response(
            fxSkemaAktif('101', 101, 'pendidikan', 'Pendidikan', 1, 'draf', [fxIsian('jumlah_siswa')]), 200
        ),
        '*' => Http::response(['data' => []], 200),
    ]);
}

/**
 * Respons berhasil `PUT /dataspasial/sektor/{id}`.
 *
 * @return array<string, mixed>
 */
function responsUbahSektor(): array
{
    return ['data' => ['id' => 101, 'type' => 'sektor', 'attributes' => [
        'kode' => 'pendidikan', 'nama' => 'Pendidikan Dasar', 'ikon' => 'school',
    ]]];
}

// -----------------------------------------------------------------------
// US7 #1 — form edit: kode baca-saja (FR-007d-5)
// -----------------------------------------------------------------------

it('menampilkan form edit dengan nama dan ikon terisi serta kode baca-saja', function () {
    fakeEditor();

    $response = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']));

    $response->assertOk();
    $response->assertSee('Pendidikan');
    $response->assertSee('form-ubah-sektor', false);

    // ⚠️ Nilai form diisi JS dari `data-sektor-*` pada item daftar, bukan
    // dirender server (FR-007d-6). Yang diuji adalah sumber datanya.
    $response->assertSee('data-sektor-nama="Pendidikan"', false);
    $response->assertSee('id="input-ubah-nama-sektor"', false);
    // Kode tampil sebagai informasi, bukan input yang bisa disunting.
    $response->assertDontSee('name="kode"', false);
    // ⚠️ Daftar sektor tidak lagi dikirim (keunikan = tugas API, spec Q13).
    $response->assertDontSee('nama_sektor_ada', false);
});

// -----------------------------------------------------------------------
// US7 #3/#4 — validasi (FR-007d-6)
// -----------------------------------------------------------------------

it('menolak nama sektor kosong', function () {
    fakeEditor();

    $response = $this->from(route('dataspasial.skema', ['sektor' => 'pendidikan']))
        ->put(route('dataspasial.skema.sektor.update', ['sektor' => 101]), [
            'nama' => '',
        ]);

    $response->assertRedirect(route('dataspasial.skema', ['sektor' => 'pendidikan']));
    $response->assertSessionHasErrors('nama');
});

it('tidak mengirim daftar sektor dan meneruskan penolakan keunikan dari API', function () {
    // ⚠️ Spec Q13 — keunikan nama adalah tanggung jawab API. OpenKab tidak
    // lagi mengirim `nama_sektor_ada` untuk melakukan pemeriksaan sendiri.
    fakeEditor(422);

    $response = $this->from(route('dataspasial.skema', ['sektor' => 'pendidikan']))
        ->put(route('dataspasial.skema.sektor.update', ['sektor' => 101]), [
            'nama' => 'Kesehatan',
        ]);

    $response->assertSessionHas('error');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'PUT'
            && ! array_key_exists('nama_sektor_ada', $request->data());
    });
});

// -----------------------------------------------------------------------
// US7 #2 — simpan sukses (FR-007d-9)
// -----------------------------------------------------------------------

it('menyimpan perubahan sektor tanpa mengirim kode sektor', function () {
    fakeEditor();

    $response = $this->put(route('dataspasial.skema.sektor.update', ['sektor' => 101]), [
        'nama' => 'Pendidikan Dasar',
        'ikon' => 'school',
    ]);

    $response->assertRedirect(route('dataspasial.skema', ['sektor' => 'pendidikan']));
    $response->assertSessionHas('success');

    // ⚠️ **Kode sektor TIDAK PERNAH dikirim** (FR-007d-5).
    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'PUT'
            && str_contains($request->url(), '/dataspasial/sektor/101')
            && $request['nama'] === 'Pendidikan Dasar'
            && ! array_key_exists('kode', $request->data());
    });
});

it('meneruskan kegagalan PUT ke service sebagai data tidak tersedia', function () {
    fakeEditor(404);

    $result = app(DataSpasialApiService::class)->ubahSektor(101, ['nama' => 'Pendidikan Dasar']);

    expect($result->isUnavailable())->toBeTrue();
    expect($result->userMessage())->toContain('tidak tersedia');
});

// -----------------------------------------------------------------------
// US7 #5 — endpoint belum ada (FR-007d-7)
// -----------------------------------------------------------------------

it('menampilkan data sementara tidak tersedia dan tidak mengubah daftar saat endpoint menolak', function () {
    // API hidup membalas 404 untuk PUT sektor.
    fakeEditor(404);

    $response = $this->from(route('dataspasial.skema', ['sektor' => 'pendidikan']))
        ->put(route('dataspasial.skema.sektor.update', ['sektor' => 101]), [
            'nama' => 'Pendidikan Dasar',
        ]);

    $response->assertRedirect(route('dataspasial.skema', ['sektor' => 'pendidikan']));
    $response->assertSessionHas('error', fn (string $m): bool => str_contains($m, 'tidak tersedia'));

    // ⚠️ Daftar sektor lama tidak ikut berubah (FR-007d-7). Formulir yang gagal
    // **memang** menampilkan kembali nilai yang diketik (`withInput`), jadi
    // yang diperiksa adalah blok daftar sektor — bukan keseluruhan halaman.
    $halaman = $this->followingRedirects()
        ->from(route('dataspasial.skema', ['sektor' => 'pendidikan']))
        ->put(route('dataspasial.skema.sektor.update', ['sektor' => 101]), [
            'nama' => 'Pendidikan Dasar',
        ]);

    preg_match('/<ul[^>]*data-testid="daftar-sektor".*?<\/ul>/s', $halaman->getContent(), $m);
    expect($m)->not->toBeEmpty('Daftar sektor tidak dirender');
    expect($m[0])->toContain('Pendidikan');
    expect($m[0])->not->toContain('Pendidikan Dasar');
});

it('menampilkan data sementara tidak tersedia saat endpoint menolak dengan 405', function () {
    fakeEditor(405);

    $this->from(route('dataspasial.skema', ['sektor' => 'pendidikan']))
        ->put(route('dataspasial.skema.sektor.update', ['sektor' => 101]), [
            'nama' => 'Pendidikan Dasar',
        ])
        ->assertSessionHas('error', fn (string $m): bool => str_contains($m, 'tidak tersedia'));
});

// -----------------------------------------------------------------------
// Otorisasi (FR-001)
// -----------------------------------------------------------------------

it('menolak peran tanpa izin tulis saat mengubah sektor', function () {
    fakeEditor();

    $operator = makeRole('operator impor us7', ['dataspasial-skema-read']);
    test()->actingAs($operator, 'web');

    $this->put(route('dataspasial.skema.sektor.update', ['sektor' => 101]), [
        'nama' => 'Pendidikan Dasar',
    ])->assertForbidden();
});
