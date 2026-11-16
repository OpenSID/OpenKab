<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(DatabaseTransactions::class);

/**
 * US2 — Riwayat versi & perbandingan dua versi (FR-050 … FR-058).
 *
 * Endpoint detail versi `GET /dataspasial/skema/{id}` **tidak ada** di API
 * (research.md R3) — isian per versi diambil lewat `?versi=` + `termasuk_arsip`.
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

    test()->actingAs(makeAdminSkema(), 'web');
});

/** Stub riwayat + isian per versi (v3 = dengan arsip, v1 = kosong). */
function fakeRiwayatPerbandingan(?array $riwayat = null): void
{
    Http::fake([
        '*/api/v1/config/*' => Http::response(fakeConfigEndpoint(), 200),
        '*/api/v1/dataspasial/skema/riwayat*' => Http::response(fakeKoleksi($riwayat ?? fxRiwayatDuaVersi()), 200),
        '*/api/v1/dataspasial/skema?*versi=3*' => Http::response(fxSkemaVersiDenganArsip(), 200),
        '*/api/v1/dataspasial/skema?*versi=1*' => Http::response([
            'data' => [
                'type' => 'skema data spasial',
                'id' => '8142',
                'attributes' => [
                    'sektor' => ['id' => 4234, 'kode' => 'SK0', 'nama' => 'Sektor 0'],
                    'versi_skema' => ['id' => 8142, 'nomor_versi' => 1, 'status' => 'draf'],
                    'isian' => [],
                ],
            ],
        ], 200),
        '*/api/v1/dataspasial/skema?*' => Http::response(fxSkemaVersiDenganArsip(), 200),
        '*' => Http::response(['data' => []], 200),
    ]);
}

// -----------------------------------------------------------------------
// Riwayat versi
// -----------------------------------------------------------------------

it('menampilkan seluruh versi sektor beserta status dan catatannya', function () {
    fakeRiwayatPerbandingan();

    $response = $this->getJson(route('dataspasial.skema.riwayat', ['sektor' => 'SK0']));

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonCount(2, 'versi');
    $response->assertJsonPath('versi.0.nomor_versi', 3);
    $response->assertJsonPath('versi.0.status_label', 'Terbit');
    $response->assertJsonPath('versi.1.nomor_versi', 1);
    $response->assertJsonPath('versi.1.status_label', 'Draf');
    $response->assertJsonPath('bisa_dibandingkan', true);
});

it('menampilkan versi usang sebagai status yang sah', function () {
    fakeRiwayatPerbandingan(fxRiwayatDenganUsang());

    $this->getJson(route('dataspasial.skema.riwayat', ['sektor' => 'SK0']))
        ->assertOk()
        ->assertJsonPath('versi.1.status', 'usang')
        ->assertJsonPath('versi.1.status_label', 'Usang');
});

it('menampilkan keterangan tanpa catatan pada versi yang tidak punya catatan', function () {
    fakeRiwayatPerbandingan(fxRiwayatDenganUsang());

    $this->getJson(route('dataspasial.skema.riwayat', ['sektor' => 'SK0']))
        ->assertOk()
        ->assertJsonPath('versi.1.catatan_label', '(tanpa catatan)');
});

it('menonaktifkan perbandingan ketika sektor hanya punya satu versi', function () {
    fakeRiwayatPerbandingan([fxRiwayatVersi('8143', 4234, 3, 'terbit')]);

    $this->getJson(route('dataspasial.skema.riwayat', ['sektor' => 'SK0']))
        ->assertOk()
        ->assertJsonPath('bisa_dibandingkan', false);
});

it('menampilkan data sementara tidak tersedia saat riwayat gagal', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(fakeConfigEndpoint(), 200),
        '*/api/v1/dataspasial/skema/riwayat*' => Http::response(['message' => 'boom'], 500),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->getJson(route('dataspasial.skema.riwayat', ['sektor' => 'SK0']))
        ->assertStatus(503)
        ->assertJsonPath('message', 'Data sementara tidak tersedia.');
});

// -----------------------------------------------------------------------
// Perbandingan dua versi
// -----------------------------------------------------------------------

it('membandingkan dua versi dan menampilkan isian terarsip', function () {
    fakeRiwayatPerbandingan();

    $response = $this->getJson(route('dataspasial.skema.perbandingan', [
        'sektor' => 'SK0',
        'dari' => 3,
        'ke' => 1,
    ]));

    $response->assertOk();
    $response->assertJsonPath('dari.nomorVersi', 3);
    $response->assertJsonPath('ke.nomorVersi', 1);

    $kode = array_column($response->json('baris'), 'kode');
    expect($kode)->toContain('arsip_lama');

    $arsip = collect($response->json('baris'))->firstWhere('kode', 'arsip_lama');
    expect($arsip['status'])->toBe('diarsipkan');

    // Isian per versi WAJIB diminta lewat `?versi=` — bukan endpoint yang 404.
    Http::assertSent(fn (Request $r): bool => ! str_contains($r->url(), '/dataspasial/skema/8143'));
});

it('menolak perbandingan dua versi yang sama', function () {
    fakeRiwayatPerbandingan();

    $this->getJson(route('dataspasial.skema.perbandingan', [
        'sektor' => 'SK0', 'dari' => 3, 'ke' => 3,
    ]))->assertStatus(422);
});

it('menyebut versi yang gagal dimuat dan tidak menampilkan perbandingan', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(fakeConfigEndpoint(), 200),
        '*/api/v1/dataspasial/skema?*versi=3*' => Http::response(fxSkemaVersiDenganArsip(), 200),
        '*/api/v1/dataspasial/skema?*versi=1*' => Http::response(['message' => 'Versi skema tidak ditemukan'], 404),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->getJson(route('dataspasial.skema.perbandingan', [
        'sektor' => 'SK0', 'dari' => 3, 'ke' => 1,
    ]))
        ->assertStatus(503)
        ->assertJsonPath('gagal_dimuat', 1)
        ->assertJsonPath('baris', null);
});

it('menolak pengguna tanpa izin tulis membuka riwayat dan perbandingan', function () {
    fakeRiwayatPerbandingan();

    test()->actingAs(makePenggunaBacaSaja(), 'web');

    $this->getJson(route('dataspasial.skema.riwayat', ['sektor' => 'SK0']))->assertForbidden();
    $this->getJson(route('dataspasial.skema.perbandingan', [
        'sektor' => 'SK0', 'dari' => 1, 'ke' => 3,
    ]))->assertForbidden();
});
