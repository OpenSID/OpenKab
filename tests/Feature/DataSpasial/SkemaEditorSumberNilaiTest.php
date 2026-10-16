<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(DatabaseTransactions::class);

/**
 * US4 / Q20 — Pemilihan sumber nilai per isian (FR-022 … FR-025).
 *
 * Skenario:
 *  #1 tombol sumber nilai membuka modal (bukan terikat pemetaan lama) — Q20
 *  #2 mode "sektor baru" adalah keadaan MENYUNTING (FR-008f)
 *  #3 template baris kosong selalu tersedia agar Tambah Isian Baru berfungsi
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

    $this->adminSkema = makeRole('admin skema sumber nilai', [
        'dataspasial-skema-read',
        'dataspasial-skema-write',
    ]);

    test()->actingAs($this->adminSkema, 'web');
});

it('menyediakan tombol sumber nilai pada setiap baris isian', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(['data' => [[
            'type' => 'daftar sektor',
            'id' => '1',
            'attributes' => ['kode' => 'pendidikan', 'nama' => 'Pendidikan', 'status' => 'aktif'],
        ]]], 200),
        '*/api/v1/dataspasial/skema?sektor=pendidikan' => Http::response(['data' => [[
            'type' => 'skema data spasial',
            'id' => '9',
            'attributes' => [
                'sektor' => ['id' => 1, 'kode' => 'pendidikan', 'nama' => 'Pendidikan'],
                'versi_skema' => ['id' => 9, 'nomor_versi' => 1, 'status' => 'draf'],
                'isian' => [[
                    'kode' => 'jumlah_siswa',
                    'label' => 'Jumlah Siswa',
                    'tipe' => 'angka',
                    'wajib' => true,
                    'urutan' => 1,
                    'tampil_popup' => false,
                    'tampil_filter' => false,
                    'tampil_indikator' => false,
                    'pemetaan' => [],
                ]],
            ],
        ]]], 200),
        '*' => Http::response(['data' => []], 200),
    ]);

    $content = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']))
        ->assertOk()
        ->getContent();

    // Tombol sumber nilai + modal Choosing Manual/Otomatis (Q20, FR-022).
    expect($content)
        ->toContain('btn-pilih-sumber-baris')
        ->toContain('data-sumber="manual"')
        ->toContain('data-tabel-sumber')
        ->toContain('data-kolom-sumber');
});

it('menyediakan template baris kosong agar tombol tambah isian berfungsi pada mode penyuntingan', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(['data' => [[
            'type' => 'daftar sektor',
            'id' => '1',
            'attributes' => ['kode' => 'pendidikan', 'nama' => 'Pendidikan', 'status' => 'aktif'],
        ]]], 200),
        '*/api/v1/dataspasial/skema?sektor=pendidikan' => Http::response(['data' => [[
            'type' => 'skema data spasial',
            'id' => '9',
            'attributes' => [
                'sektor' => ['id' => 1, 'kode' => 'pendidikan', 'nama' => 'Pendidikan'],
                'versi_skema' => ['id' => 9, 'nomor_versi' => 2, 'status' => 'terbit'],
                'isian' => [[
                    'kode' => 'jumlah_siswa',
                    'label' => 'Jumlah Siswa',
                    'tipe' => 'angka',
                    'urutan' => 1,
                    'pemetaan' => [],
                ]],
            ],
        ]]], 200),
        '*' => Http::response(['data' => []], 200),
    ]);

    $content = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']))
        ->assertOk()
        ->getContent();

    // ⚠️ Template WAJIB ada walau skema `terbit` — tanpa itu handler Tambah
    // Isian bail-out dan tombol tampak tidak berfungsi (FR-008c/FR-008f).
    expect($content)
        ->toContain('id="template-baris-isian"')
        ->toContain('id="btn-tambah-isian"')
        ->toContain('id="btn-ubah"');
});

it('menyimpan sumber nilai otomatis beserta tabel dan kolomnya', function () {
    Http::fake([
        '*/api/v1/dataspasial/skema' => Http::response([
            'success' => true,
            'data' => ['type' => 'ds_skema_versi', 'id' => '9'],
        ], 201),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->postJson(route('dataspasial.skema.draf.store'), [
        'sektor' => 'pendidikan',
        'sektor_nama' => 'Pendidikan',
        'isian' => [[
            'kode' => 'jumlah_siswa',
            'label' => 'Jumlah Siswa',
            'tipe' => 'angka',
            'urutan' => 1,
            'sumber' => 'openSID',
            'tabelSumber' => 'posyandu',
            'kolomSumber' => 'jenis',
        ]],
    ])->assertOk()->assertJsonPath('success', true);

    Http::assertSent(function ($request) {
        $body = json_decode((string) $request->body(), true);
        $pemetaan = $body['isian'][0]['pemetaan'] ?? [];

        return str_ends_with($request->url(), '/api/v1/dataspasial/skema')
            && ($pemetaan[0]['sumber'] ?? null) === 'openSID'
            && ($pemetaan[0]['tabel_sumber'] ?? null) === 'posyandu'
            && ($pemetaan[0]['kolom_sumber'] ?? null) === 'jenis';
    });
});
