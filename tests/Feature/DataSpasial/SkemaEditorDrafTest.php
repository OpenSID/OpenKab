<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(DatabaseTransactions::class);

/**
 * US3 — Menyimpan draf & membukanya kembali (FR-029 … FR-033, Q21).
 *
 *  #1/2 draf tersimpan dapat dibuka kembali dengan isi utuh (FR-030/SC-004)
 *  #3 simpan gagal → isi editor tetap utuh & dapat dicoba ulang (FR-041b)
 *  #4 API menolak (422) → penjelasan tampil, isi tidak hilang (FR-021)
 *
 * ⚠️ Q21 — seluruh aksi tulis dikirim lewat AJAX dan backend membalas JSON.
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

    $this->adminSkema = makeRole('admin skema us3', [
        'dataspasial-skema-read',
        'dataspasial-skema-write',
    ]);

    test()->actingAs($this->adminSkema, 'web');

    $this->urlDraf = route('dataspasial.skema.draf.store');
});

/**
 * @param  array<int, array<string, mixed>>  $isian
 * @return array<string, mixed>
 */
function drafUs3(array $isian): array
{
    return ['sektor' => 'pendidikan', 'sektor_nama' => 'Pendidikan', 'isian' => $isian];
}

/**
 * @return array<string, mixed>
 */
function isianUs3(): array
{
    return [
        'kode' => 'jumlah_siswa',
        'label' => 'Jumlah Siswa',
        'tipe' => 'angka',
        'wajib' => true,
        'satuan' => 'orang',
        'urutan' => 1,
        'popupPeta' => true,
        'sebagaiFilter' => false,
        'sebagaiIndikator' => true,
    ];
}

it('menyimpan draf lewat AJAX dan membalas JSON tanpa muat ulang halaman', function () {
    Http::fake([
        '*/api/v1/dataspasial/skema' => Http::response([
            'success' => true,
            'data' => ['type' => 'ds_skema_versi', 'id' => '9'],
        ], 201),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->postJson($this->urlDraf, drafUs3([isianUs3()]))
        ->assertOk()
        ->assertJsonPath('success', true)
        ->assertJsonPath('sektor', 'pendidikan');

    // ⚠️ Q23 — nomor versi tidak lagi dikirim/diisi oleh OpenKab (Q18).
    Http::assertSent(function ($request) {
        $body = json_decode((string) $request->body(), true);

        return str_ends_with($request->url(), '/api/v1/dataspasial/skema')
            && is_array($body)
            && array_key_exists('sektor', $body)
            && array_key_exists('sektor_nama', $body)
            && array_key_exists('keterangan', $body)
            && ! array_key_exists('nomor_versi', $body)
            && ! array_key_exists('catatan_perubahan', $body);
    });
});

it('mempertahankan isi editor dan membalas pesan generik saat sumber mati', function () {
    Http::fake([
        '*/api/v1/dataspasial/skema' => Http::response(
            ['message' => 'Modul DataSpasial belum terpasang'],
            503
        ),
        '*' => Http::response(['data' => []], 200),
    ]);

    // FR-041b — status 503 + pesan generik; isi editor (baris isian) TIDAK hilang
    // karena frontend tidak me-reload halaman.
    $this->postJson($this->urlDraf, drafUs3([isianUs3()]))
        ->assertStatus(503)
        ->assertJsonPath('success', false)
        ->assertJsonFragment(['message' => 'Data sementara tidak tersedia. Silakan coba lagi nanti.']);
});

it('menampilkan pesan yang dapat ditindaklanjuti tanpa membocorkan detail teknis', function () {
    Http::fake([
        '*/api/v1/dataspasial/skema' => Http::response([
            'success' => false,
            'message' => 'Validasi gagal.',
            'errors' => ['sektor_nama' => ['nama sektor sudah dipakai sektor lain.']],
        ], 422),
        '*' => Http::response(['data' => []], 200),
    ]);

    $response = $this->postJson($this->urlDraf, drafUs3([isianUs3()]));

    // FR-021/FR-005 — pesan yang dapat ditindaklanjuti, BUKAN pesan teknis API
    // mentah-mentah (detail teknis hanya masuk log, FR-038).
    $response->assertStatus(503)->assertJsonPath('success', false);

    $isi = $response->getContent();
    expect($isi)
        ->toContain('Data sementara tidak tersedia')
        ->not->toContain('nama sektor sudah dipakai sektor lain');
});

it('menolak draf tanpa isian sebelum permintaan dikirim', function () {
    // Q23/FR-019/SC-020 — nol isian ditolak di layar.
    Http::fake();

    $this->postJson($this->urlDraf, drafUs3([]))
        ->assertStatus(422)
        ->assertJsonValidationErrors('isian');

    Http::assertNothingSent();
});
