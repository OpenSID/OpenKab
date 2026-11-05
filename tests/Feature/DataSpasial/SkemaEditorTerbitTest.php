<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(DatabaseTransactions::class);

/**
 * US8 — Menerbitkan draf skema (FR-041 … FR-045).
 *
 * Skenario yang diuji (spec.md US8):
 *  #1 tombol Terbitan aktif saat skema berstatus draf
 *  #2 konfirmasi menyebut nama sektor & nomor versi (FR-042)
 *  #4 skema `terbit` → tombol nonaktif & isian baca-saja
 *  #5 sumber data tidak tersedia → tombol nonaktif
 *  #6 tombol nonaktif bila ada perubahan belum disimpan (FR-045)
 *  plus penerbitan sukses → versi `terbit`
 *
 * ⚠️ **UTANG KONTRAK U6** — `POST /dataspasial/skema/{sektor}/terbit` belum ada
 * di kontrak v0.1.0 maupun API hidup. `{sektor}` adalah **kode** sektor.
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

    $this->adminSkema = makeRole('admin skema us8', [
        'dataspasial-skema-read',
        'dataspasial-skema-write',
        'dataspasial-skema-edit',
        'dataspasial-skema-delete',
    ]);

    test()->actingAs($this->adminSkema, 'web');
});

/**
 * Pasang stub HTTP halaman editor + endpoint terbitan.
 *
 * ⚠️ `Http::fake()` bersifat FIFO dan awalan `POST ` diperlakukan sebagai glob
 * (bukan filter method), sehingga pemisahan dilakukan di dalam closure.
 *
 * @see SkemaEditorEditSektorTest.php untuk penjelasan lengkap.
 *
 * @param  array<int, array<string, mixed>>  $riwayat
 */
function fakeTerbit(?int $statusTerbit = null, ?array $riwayat = null, string $statusAktif = 'draf'): void
{
    $riwayat ??= [fxRiwayatVersi('901', 101, 1, 'draf')];
    $nomorVersi = (int) ($riwayat[0]['attributes']['nomor_versi'] ?? 1);

    Http::fake([
        '*/api/v1/dataspasial/skema/*/terbit' => function (Request $request) use ($statusTerbit) {
            if ($request->method() !== 'POST') {
                return Http::response(['data' => []], 200);
            }

            return $statusTerbit === null
                ? Http::response([
                    'data' => ['id' => '902', 'type' => 'skema', 'attributes' => [
                        'versi_skema' => ['nomor_versi' => 1, 'status' => 'terbit'],
                    ]],
                ], 200)
                : Http::response(['message' => 'Gagal'], $statusTerbit);
        },
        '*/api/v1/config/*' => Http::response(fakeConfigEndpoint(), 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(
            fakeKoleksi([fxSektor('101', 'pendidikan', 'Pendidikan')]), 200
        ),
        '*/api/v1/dataspasial/skema/riwayat*' => Http::response(fakeKoleksi($riwayat), 200),
        '*/api/v1/dataspasial/skema?*' => Http::response(
            fxSkemaAktif('101', 101, 'pendidikan', 'Pendidikan', $nomorVersi, $statusAktif, [fxIsian('jumlah_siswa')]),
            200
        ),
        '*' => Http::response(['data' => []], 200),
    ]);
}

// -----------------------------------------------------------------------
// US8 #1 — tombol aktif saat draf (FR-041)
// -----------------------------------------------------------------------

it('mengaktifkan tombol terbitkan saat skema berstatus draf', function () {
    fakeTerbit();

    $response = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']));

    $response->assertOk();

    // Tombol TIDAK lagi memakai atribut `disabled` statis.
    preg_match('/<button[^>]*id="btn-terbitan"[^>]*>/', $response->getContent(), $m);
    expect($m)->not->toBeEmpty('Tombol Terbitan tidak dirender');
    expect($m[0])->not->toContain('disabled');
});

it('tidak menyertakan catatan KAB-03 pada tombol terbitkan', function () {
    fakeTerbit();

    $response = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']));

    $response->assertDontSee('KAB-03');
});

// -----------------------------------------------------------------------
// US8 #4 — skema terbit → nonaktif (FR-008a)
// -----------------------------------------------------------------------

it('menonaktifkan tombol terbitkan ketika skema sudah terbit', function () {
    fakeTerbit(riwayat: [fxRiwayatVersi('901', 101, 3, 'terbit')], statusAktif: 'terbit');

    $response = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']));

    $response->assertOk();
    preg_match('/<button[^>]*id="btn-terbitan"[^>]*>/', $response->getContent(), $m);
    expect($m)->not->toBeEmpty();
    expect($m[0])->toContain('disabled');
});

// -----------------------------------------------------------------------
// US8 #2 — penerbitan sukses
// -----------------------------------------------------------------------

it('menerbitkan draf memakai kode sektor pada path', function () {
    fakeTerbit(riwayat: [fxRiwayatVersi('902', 101, 1, 'terbit')]);

    $response = $this->from(route('dataspasial.skema', ['sektor' => 'pendidikan']))
        ->post(route('dataspasial.skema.terbit'), [
            'sektor' => 'pendidikan',
            'skema' => 901,
            'catatan_perubahan' => 'Versi 1 terbit',
        ]);

    // Setelah terbit, editor diarahkan ke versi yang baru terbit.
    $response->assertRedirect(route('dataspasial.skema', [
        'sektor' => 'pendidikan',
        'skema' => 902,
    ]));
    $response->assertSessionHas('success');

    // ⚠️ `{sektor}` pada path adalah KODE sektor, bukan id numerik.
    Http::assertSent(function (Request $request): bool {
        return $request->method() === 'POST'
            && str_ends_with($request->url(), '/dataspasial/skema/pendidikan/terbit');
    });
});

it('menampilkan data sementara tidak tersedia saat endpoint terbitan 404', function () {
    fakeTerbit(404);

    $this->from(route('dataspasial.skema', ['sektor' => 'pendidikan']))
        ->post(route('dataspasial.skema.terbit'), [
            'sektor' => 'pendidikan',
            'skema' => 901,
        ])
        ->assertSessionHas('error', fn (string $m): bool => str_contains($m, 'tidak tersedia'));
});

it('menampilkan data sementara tidak tersedia saat endpoint terbitan 405', function () {
    fakeTerbit(405);

    $this->from(route('dataspasial.skema', ['sektor' => 'pendidikan']))
        ->post(route('dataspasial.skema.terbit'), [
            'sektor' => 'pendidikan',
            'skema' => 901,
        ])
        ->assertSessionHas('error');
});

// -----------------------------------------------------------------------
// Otorisasi (FR-001)
// -----------------------------------------------------------------------

it('menolak peran tanpa izin tulis saat menerbitkan', function () {
    fakeTerbit();

    $operator = makeRole('operator impor us8', ['dataspasial-skema-read']);
    test()->actingAs($operator, 'web');

    $this->post(route('dataspasial.skema.terbit'), [
        'sektor' => 'pendidikan',
        'skema' => 901,
    ])->assertForbidden();
});
