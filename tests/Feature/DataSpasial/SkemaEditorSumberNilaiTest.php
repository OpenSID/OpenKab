<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Services\SkemaEditorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
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
            // ⚠️ Entitas `ds_pemetaan` hanya punya SATU kolom `rujukan_sumber`
            // berbentuk "tabel.kolom" (§6a.4) — bukan `tabel_sumber`/
            // `kolom_sumber` terpisah.
            && ($pemetaan[0]['rujukan_sumber'] ?? null) === 'posyandu.jenis';
    });
});

it('menyediakan kolom nama sektor walau belum ada sektor yang dipilih', function () {
    // Regresi FR-008f/Q26 — saat "Tambah Sektor Baru" ditekan tanpa pernah
    // memilih sektor, kolom nama sektor WAJIB ada di DOM (dibuka oleh JS).
    // Bila hanya dirender bila `$sektorTerpilih`, modal/mode ini tidak punya
    // kolom untuk diisi sehingga layar tampak tidak berubah sama sekali.
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(['data' => [[
            'type' => 'daftar sektor',
            'id' => '1',
            'attributes' => ['kode' => 'pendidikan', 'nama' => 'Pendidikan', 'status' => 'aktif'],
        ]]], 200),
        '*' => Http::response(['data' => []], 200),
    ]);

    $content = $this->get(route('dataspasial.skema'))
        ->assertOk()
        ->getContent();

    expect($content)
        ->toContain('id="wadah-input-sektor-nama"')
        ->toContain('id="input-sektor-nama-editor"')
        ->toContain('id="btn-tambah-sektor"')
        // Pesan keadaan kosong tetap ada sebagai konteks awal.
        ->toContain('isian-belum-pilih-sektor');
});

it('menyembunyikan kolom nama sektor pada keadaan awal tanpa sektor terpilih', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(['data' => [[
            'type' => 'daftar sektor',
            'id' => '1',
            'attributes' => ['kode' => 'pendidikan', 'nama' => 'Pendidikan', 'status' => 'aktif'],
        ]]], 200),
        '*' => Http::response(['data' => []], 200),
    ]);

    $content = $this->get(route('dataspasial.skema'))->assertOk()->getContent();

    // Kolom TETAP ada di DOM namun tersembunyi sampai mode "sektor baru" aktif.
    preg_match('/<div[^>]*id="wadah-input-sektor-nama"[^>]*>/', $content, $m);
    expect($m)->not->toBeEmpty();
    expect($m[0])->toContain('d-none');
});

// ---------------------------------------------------------------------------
// rujukan_sumber — pemetaan baca ↔ tulis (verified §6a.4)
// ---------------------------------------------------------------------------

it('memecah rujukan_sumber menjadi tabel dan kolom untuk ditampilkan', function () {
    $service = app(SkemaEditorService::class);

    $isian = $service->petakanIsian([
        'id' => '8484',
        'attributes' => [
            'kode' => 'lebar_jalan',
            'label' => 'Lebar Jalan',
            'tipe' => 'angka',
            'urutan' => 1,
            // ⚠️ Bentuk nyata API: `rujukan_sumber` berupa "tabel.kolom".
            'pemetaan' => [[
                'id' => '3951',
                'sumber' => 'openSID',
                'rujukan_sumber' => 'tbl_jalan.lebar',
            ]],
        ],
    ]);

    expect($isian['sumber'])->toBe('openSID')
        ->and($isian['rujukan'])->toBe('tbl_jalan.lebar')
        ->and($isian['tabelSumber'])->toBe('tbl_jalan')
        ->and($isian['kolomSumber'])->toBe('lebar');
});

it('mempertahankan rujukan bertingkat tanpa merusak nilai saat disimpan kembali', function () {
    $service = app(SkemaEditorService::class);

    $isian = $service->petakanIsian([
        'id' => '1',
        'attributes' => [
            'kode' => 'kota',
            'label' => 'Kota',
            'tipe' => 'teks',
            'urutan' => 1,
            'pemetaan' => [[
                'sumber' => 'impor',
                // Berisi lebih dari satu titik — pemecahan memakai titik terakhir
                // supaya nilainya pulih utuh saat disambung kembali.
                'rujukan_sumber' => 'ds_ref.kota.nama',
            ]],
        ],
    ]);

    $payload = $service->composeDrafPayload([$isian], [
        'sektor' => 'pendidikan',
        'sektor_nama' => 'Pendidikan',
    ]);

    expect($isian['tabelSumber'])->toBe('ds_ref.kota')
        ->and($isian['kolomSumber'])->toBe('nama')
        ->and($payload['isian'][0]['pemetaan'][0]['rujukan_sumber'])->toBe('ds_ref.kota.nama');
});

it('menyimpan pemetaan memakai satu nilai rujukan_sumber dari rujukan', function () {
    $service = app(SkemaEditorService::class);

    $payload = $service->composeDrafPayload([[
        'kode' => 'lebar_jalan',
        'label' => 'Lebar Jalan',
        'tipe' => 'angka',
        'wajib' => false,
        'sumber' => 'openSID',
        'rujukan' => 'tbl_jalan.lebar',
    ]], ['sektor' => 'pendidikan', 'sektor_nama' => 'Pendidikan']);

    $pemetaan = $payload['isian'][0]['pemetaan'];

    expect($pemetaan)->toHaveCount(1)
        ->and($pemetaan[0]['sumber'])->toBe('openSID')
        ->and($pemetaan[0]['rujukan_sumber'])->toBe('tbl_jalan.lebar')
        // ⚠️ API hanya punya `rujukan_sumber` — tidak ada `tabel_sumber`/
        // `kolom_sumber` terpisah (§6a.4).
        ->and($pemetaan[0])->not->toHaveKey('tabel_sumber')
        ->and($pemetaan[0])->not->toHaveKey('kolom_sumber');
});

it('menyambung tabel dan kolom menjadi rujukan_sumber bila klien tidak mengirim rujukan', function () {
    $service = app(SkemaEditorService::class);

    $payload = $service->composeDrafPayload([[
        'kode' => 'lebar_jalan',
        'label' => 'Lebar Jalan',
        'tipe' => 'angka',
        'sumber' => 'dataPresisi',
        'tabelSumber' => 'tbl_jalan',
        'kolomSumber' => 'lebar',
    ]], ['sektor' => 'pendidikan', 'sektor_nama' => 'Pendidikan']);

    expect($payload['isian'][0]['pemetaan'][0]['rujukan_sumber'])->toBe('tbl_jalan.lebar');
});

it('tidak mengirim pemetaan bila sumber nilai manual', function () {
    $service = app(SkemaEditorService::class);

    $payload = $service->composeDrafPayload([[
        'kode' => 'keterangan',
        'label' => 'Keterangan',
        'tipe' => 'teks',
        'sumber' => 'manual',
    ]], ['sektor' => 'pendidikan', 'sektor_nama' => 'Pendidikan']);

    expect($payload['isian'][0]['pemetaan'])->toBeNull();
});

it('menolak sumber nilai otomatis tanpa rujukan', function () {
    $payload = [
        'sektor_nama' => 'Pendidikan',
        'isian' => [[
            'kode' => 'lebar_jalan',
            'label' => 'Lebar Jalan',
            'tipe' => 'angka',
            'wajib' => false,
            'urutan' => 1,
            'sumber' => 'openSID',
        ]],
    ];

    $this->postJson(route('dataspasial.skema.draf.store'), $payload)
        ->assertStatus(422)
        ->assertJsonValidationErrors('isian.0.rujukan');
});

it('menerima sumber nilai otomatis beserta rujukannya', function () {
    Http::fake([
        '*' => Http::response(['data' => ['id' => '9', 'attributes' => ['kode' => 'pendidikan']]], 201),
    ]);

    $this->postJson(route('dataspasial.skema.draf.store'), [
        'sektor' => 'pendidikan',
        'sektor_nama' => 'Pendidikan',
        'isian' => [[
            'kode' => 'lebar_jalan',
            'label' => 'Lebar Jalan',
            'tipe' => 'angka',
            'wajib' => false,
            'urutan' => 1,
            'sumber' => 'openSID',
            'rujukan' => 'tbl_jalan.lebar',
        ]],
    ])->assertOk();

    Http::assertSent(function (Request $request): bool {
        $isi = $request->data();

        return ($isi['isian'][0]['pemetaan'][0]['rujukan_sumber'] ?? null) === 'tbl_jalan.lebar';
    });
});

it('menampilkan tabel dan kolom hasil pemecahan rujukan_sumber pada baris isian', function () {
    fakeEndpointEditor(
        [fxSektor('1', 'pendidikan', 'Pendidikan', 1, 1)],
        [],
        [
            'data' => [[
                'type' => 'ds_skema_versi',
                'id' => '9',
                'attributes' => [
                    'sektor' => ['id' => '1', 'kode' => 'pendidikan', 'nama' => 'Pendidikan'],
                    'versi_skema' => ['id' => '9', 'nomor_versi' => 1, 'status' => 'terbit'],
                    'isian' => [fxIsian('lebar_jalan', [
                        'id' => '8484',
                        'label' => 'Lebar Jalan',
                        'tipe' => 'angka',
                        'satuan' => 'meter',
                        'pemetaan' => [[
                            'id' => '3951',
                            'sumber' => 'openSID',
                            // ⚠️ Bentuk nyata API — satu string "tabel.kolom" (§6a.4).
                            'rujukan_sumber' => 'tbl_jalan.lebar',
                        ]],
                    ])],
                ],
            ]],
        ],
    );

    $content = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']))
        ->assertOk()
        ->getContent();

    // ⚠️ Kedua kolom harus terisi dari `rujukan_sumber` — inilah yang membuat
    // modal sumber nilai terbuka pada nilai lama saat diklik lagi.
    expect($content)
        ->toContain('data-tabel-sumber="tbl_jalan"')
        ->toContain('data-kolom-sumber="lebar"')
        ->toContain('data-rujukan="tbl_jalan.lebar"')
        // Skema `terbit` bersifat baca-saja, jadi rujukan ditampilkan sebagai
        // teks "tabel.kolom" alih-alih dua input (FR-008a).
        ->toMatch('/text-muted small d-block">\s*tbl_jalan\.lebar\s*<\/span>/');
});
