<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(DatabaseTransactions::class);

/**
 * US1 — Layar terbitan: ringkasan perubahan, konfirmasi, dan penerbitan (FR-017 … FR-023).
 *
 * ⚠️ Sesuai clarify 2026-10-16, seluruh layar ini adalah DIALOG di atas halaman
 * `dataspasial/skema` yang dipicu tombol `#btn-terbitan` KAB-02 — bukan halaman baru.
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

    test()->adminSkema = makeAdminSkema();
    test()->actingAs(test()->adminSkema, 'web');
});

/**
 * Stub: daftar sektor, riwayat 2 versi (v3 terbit + v1 draf), dan isian versi
 * terbit memakai fixture nyata contracts §4.
 */
function fakeTerbitan(int $statusTerbit = 200, ?array $isis = null): void
{
    $terbitResponse = $statusTerbit === 200
        ? [
            'data' => [
                'type' => 'skema data spasial',
                'id' => '8143',
                'attributes' => [
                    'sektor' => ['id' => 4234, 'kode' => 'SK0', 'nama' => 'Sektor 0'],
                    'versi_skema' => [
                        'id' => 8143,
                        'nomor_versi' => 3,
                        'status' => 'terbit',
                        'catatan_perubahan' => 'Tambah isian guru.',
                        'diterbitkan_pada' => '2025-10-05T14:00:00.000000Z',
                        'diterbitkan_oleh' => 'Budi Santoso',
                    ],
                    'isian' => $isis ?? [],
                ],
            ],
        ]
        : ['message' => 'Tidak ada draf aktif untuk sektor SK0.'];

    Http::fake([
        '*/api/v1/dataspasial/skema/*/terbit' => function (Request $request) use ($statusTerbit, $terbitResponse) {
            if ($request->method() !== 'POST') {
                return Http::response(['data' => []], 200);
            }

            return $statusTerbit === 200
                ? Http::response($terbitResponse, 200)
                : Http::response($terbitResponse, $statusTerbit);
        },
        // Utang U10 — endpoint pra-terbit belum ada di API.
        '*/api/v1/dataspasial/skema/*/pra-terbit*' => Http::response(['message' => 'Not found'], 404),
        '*/api/v1/dataspasial/skema/terbit/preview' => Http::response(['data' => []], 200),
        '*/api/v1/config/*' => Http::response(fakeConfigEndpoint(), 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(fakeKoleksi([fxSektor('4234', 'SK0', 'Sektor 0')]), 200),
        '*/api/v1/dataspasial/skema/riwayat*' => Http::response(fakeKoleksi(fxRiwayatDuaVersi()), 200),
        '*/api/v1/dataspasial/skema?*' => Http::response(fxSkemaVersiDenganArsip(), 200),
        '*' => Http::response(['data' => []], 200),
    ]);
}

// -----------------------------------------------------------------------
// Kerangka dialog pada halaman skema (FR-017, FR-046)
// -----------------------------------------------------------------------

it('merender dialog layar terbitan pada halaman skema tanpa memuat halaman baru', function () {
    fakeTerbitan();

    $response = $this->get(route('dataspasial.skema', ['sektor' => 'SK0']));

    $response->assertOk();
    $response->assertSee('data-testid="layar-terbitan"', false);
    $response->assertSee('data-testid="ringkasan-perubahan"', false);
    $response->assertSee('data-testid="catatan-perubahan"', false);
    $response->assertSee('data-testid="tabel-adopsi"', false);
    $response->assertSee('data-testid="btn-kirim-terbit"', false);
});

it('menyertakan skrip layar terbitan pada halaman skema', function () {
    fakeTerbitan();

    $this->get(route('dataspasial.skema', ['sektor' => 'SK0']))
        ->assertSee('js/dataspasial/terbit-dialog.js', false);
});

// -----------------------------------------------------------------------
// Preview ringkasan (FR-010 … FR-016)
// -----------------------------------------------------------------------

it('mengembalikan ringkasan perubahan sebagai json', function () {
    fakeTerbitan();

    $response = $this->postJson(route('dataspasial.skema.terbit.preview'), [
        'sektor' => 'SK0',
        'isian' => [[
            'kode' => 'jumlah_siswa',
            'label' => 'Jumlah Siswa',
            'tipe' => 'angka',
            'wajib' => false,
            'urutan' => 1,
        ]],
    ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('ringkasan.versi_terbit_sebelumnya', 3);
    $response->assertJsonPath('ringkasan.ada_perubahan', true);
    $response->assertJsonPath('ringkasan.terbitan_pertama', false);
});

it('menolak preview tanpa ada perubahan terhadap versi terbit', function () {
    fakeTerbitan();

    $response = $this->postJson(route('dataspasial.skema.terbit.preview'), [
        'sektor' => 'SK0',
        'isian' => [[
            'kode' => 'lebar_jalan',
            'label' => 'Lebar Jalan',
            'tipe' => 'angka',
            'wajib' => true,
            'satuan' => 'meter',
            'urutan' => 1,
            'tampil_popup' => true,
            // Pemetaan harus sama dengan versi terbit agar selisih benar-benar nol.
            'pemetaan' => [[
                'sumber' => 'openSID',
                'rujukan_sumber' => 'tbl_jalan.lebar',
                'aturan_hitung' => null,
            ]],
        ]],
    ]);

    $response->assertStatus(422);
    $response->assertJsonPath('tanpa_perubahan', true);
});

it('menolak preview saat sektor tidak memiliki draf berjalan', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(fakeConfigEndpoint(), 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(fakeKoleksi([fxSektor('4234', 'SK0', 'Sektor 0')]), 200),
        '*/api/v1/dataspasial/skema/riwayat*' => Http::response(
            fakeKoleksi([fxRiwayatVersi('8143', 4234, 3, 'terbit')]), 200
        ),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->postJson(route('dataspasial.skema.terbit.preview'), ['sektor' => 'SK0'])
        ->assertStatus(409)
        ->assertJsonPath('success', false);
});

it('menampilkan data sementara tidak tersedia saat riwayat versi gagal dimuat', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(fakeConfigEndpoint(), 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(fakeKoleksi([fxSektor('4234', 'SK0', 'Sektor 0')]), 200),
        '*/api/v1/dataspasial/skema/riwayat*' => Http::response(['message' => 'boom'], 500),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->postJson(route('dataspasial.skema.terbit.preview'), ['sektor' => 'SK0'])
        ->assertStatus(503)
        ->assertJsonPath('message', 'Data sementara tidak tersedia.');
});

// -----------------------------------------------------------------------
// Penerbitan (FR-017 … FR-023)
// -----------------------------------------------------------------------

it('menerbitkan draf lewat ajax tanpa memuat ulang halaman', function () {
    fakeTerbitan();

    $response = $this->postJson(route('dataspasial.skema.terbit'), [
        'sektor' => 'SK0',
        'catatan_perubahan' => 'Tambah isian jumlah_siswa.',
    ]);

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('sektor', 'SK0');

    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && str_ends_with($request->url(), '/dataspasial/skema/SK0/terbit');
    });
});

it('tidak pernah mengirim identitas penerbit dari klien', function () {
    fakeTerbitan();

    $this->postJson(route('dataspasial.skema.terbit'), [
        'sektor' => 'SK0',
        'catatan_perubahan' => 'Catatan yang sah untuk uji.',
    ])->assertOk();

    Http::assertSent(function (Request $request): bool {
        if (! str_ends_with($request->url(), '/dataspasial/skema/SK0/terbit') || $request->method() !== 'POST') {
            return true;
        }

        // `diterbitkan_oleh` diisi server dari pengguna terautentikasi (FR-019).
        expect($request->data())->not->toHaveKey('diterbitkan_oleh');
        expect($request->data())->not->toHaveKey('nomor_versi');

        return true;
    });
});

it('menolak catatan perubahan kosong atau terlalu pendek sebelum mengirim', function () {
    fakeTerbitan();

    $this->postJson(route('dataspasial.skema.terbit'), ['sektor' => 'SK0'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('catatan_perubahan');

    $this->postJson(route('dataspasial.skema.terbit'), [
        'sektor' => 'SK0',
        'catatan_perubahan' => 'abc',
    ])->assertStatus(422);

    Http::assertNotSent(function (Request $request): bool {
        return $request->method() === 'POST' && str_contains($request->url(), '/terbit');
    });
});

it('menjelaskan draf yang sudah tidak aktif tanpa membuat versi baru', function () {
    fakeTerbitan(409);

    $response = $this->postJson(route('dataspasial.skema.terbit'), [
        'sektor' => 'SK0',
        'catatan_perubahan' => 'Catatan yang sah untuk uji.',
    ]);

    $response->assertStatus(503);
    $response->assertJsonPath('success', false);
    expect(strtolower((string) $response->json('message')))->toContain('draf sudah tidak aktif');
});

it('menjelaskan sektor yang tidak ditemukan di sumber data', function () {
    fakeTerbitan(404);

    $this->postJson(route('dataspasial.skema.terbit'), [
        'sektor' => 'SK0',
        'catatan_perubahan' => 'Catatan yang sah untuk uji.',
    ])->assertStatus(503)->assertJsonPath('message', 'Sektor tidak ditemukan di sumber data.');
});

it('menampilkan data sementara tidak tersedia saat sumber mati', function () {
    fakeTerbitan(500);

    $this->postJson(route('dataspasial.skema.terbit'), [
        'sektor' => 'SK0',
        'catatan_perubahan' => 'Catatan yang sah untuk uji.',
    ])->assertStatus(503)->assertJsonPath('message', 'Data sementara tidak tersedia.');
});

// -----------------------------------------------------------------------
// Otorisasi (FR-002, FR-003, SC-001)
// -----------------------------------------------------------------------

it('menolak pengguna hanya baca membuka preview terbitan dan tabel adopsi', function () {
    fakeTerbitan();

    test()->actingAs(makePenggunaBacaSaja(), 'web');

    $this->postJson(route('dataspasial.skema.terbit.preview'), ['sektor' => 'SK0'])->assertForbidden();
    $this->getJson(route('dataspasial.skema.adopsi', ['sektor' => 'SK0']))->assertForbidden();
    $this->getJson(route('dataspasial.skema.adopsi.export', ['sektor' => 'SK0']))->assertForbidden();
});

it('menolak peran tanpa izin tulis saat menerbitkan', function () {
    fakeTerbitan();

    test()->actingAs(makePenggunaBacaSaja(), 'web');

    $this->postJson(route('dataspasial.skema.terbit'), [
        'sektor' => 'SK0',
        'catatan_perubahan' => 'Catatan yang sah untuk uji.',
    ])->assertForbidden();
});
