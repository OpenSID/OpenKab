<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Models\User;
use App\Services\SkemaEditorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(DatabaseTransactions::class);

/**
 * US1 — Daftar sektor, pilih skema, buka editor isian.
 *
 * Skenario yang diuji (spec.md US1):
 *  #1 daftar sektor tampil dengan label versi/draf
 *  #2 daftar skema HANYA muncul setelah sektor dipilih (FR-007a)
 *  #7 daftar skema berganti saat sektor lain dipilih (FR-007a)
 *  #3 skema `terbit` tampil baca-saja (FR-008a)
 *  #6 unavailable → "data sementara tidak tersedia", tombol simpan nonaktif (FR-005)
 *  #5 `empty` → keadaan kosong (FR-006)
 *  #8–#12 pembuatan sektor & draf
 *
 * Bentuk respons disalin dari `contracts/skema-editor-api.md`.
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

    // ⚠️ `setPermissionsTeamId()` bersifat global per request, jadi peran yang
    // dibuat TERAKHIR harusLAH pengguna yang sedang login. Karena itu peran
    // Operator Impor & Analis OPD disiapkan lebih dulu.
    $this->operatorImpor = makeRole('operator impor us1', ['dataspasial-impor-read', 'dataspasial-impor-write']);
    $this->analisOpd = makeRole('analis opd us1', ['dataspasial-peta-read']);

    // Admin Skema = pengguna AKTIF untuk mayoritas test.
    $this->adminSkema = actingAsRole('admin skema us1', [
        'dataspasial-skema-read',
        'dataspasial-skema-write',
        'dataspasial-skema-edit',
        'dataspasial-skema-delete',
    ]);
});

/**
 * @param  array<int, string>  $permissions
 */
function actingAsRole(string $teamName, array $permissions): User
{
    $user = makeRole($teamName, $permissions);

    test()->actingAs($user, 'web');

    return $user;
}

/**
 * Beralih ke pengguna yang disiapkan pada `beforeEach`.
 *
 * `setPermissionsTeamId()` bersifat global, jadi konteks izin harus ikut
 * dipindahkan agar spatie teams menyelesaikan peran pengguna yang benar.
 */
function actingAs(User $user): void
{
    setPermissionsTeamId($user->teams()->first()?->id);

    test()->actingAs($user, 'web');
}

/**
 * @return array<string, mixed>
 */
function itemSektor(string $id, string $kode, string $nama, string $status = 'aktif'): array
{
    return [
        'type' => 'ds_sektor',
        'id' => $id,
        'attributes' => ['kode' => $kode, 'nama' => $nama, 'ikon' => null, 'status' => $status],
    ];
}

/**
 * @return array<string, mixed>
 */
/**
 * Entri riwayat versi — bentuk nyata `GET /skema/riwayat` (Q9).
 */
function itemRiwayat(string $id, int $sektorId, int $versi, string $status): array
{
    return [
        'type' => 'riwayat versi skema',
        'id' => $id,
        'attributes' => [
            'sektor_id' => $sektorId,
            'nomor_versi' => $versi,
            'status' => $status,
            'catatan_perubahan' => 'Draf uji.',
            'diterbitkan_pada' => $status === 'terbit' ? '2025-10-01T09:00:00+07:00' : null,
            'diterbitkan_oleh' => $status === 'terbit' ? 'admin@opengkab.go.id' : null,
        ],
    ];
}

/**
 * Versi aktif beserta isian — bentuk nyata `GET /skema?sektor=` (K2/K3).
 *
 * @param  array<int, array<string, mixed>>  $isian
 * @return array<string, mixed>
 */
function itemSkemaAktif(string $id, string $sektorKode, string $sektorNama, int $sektorId, int $versi, string $status, array $isian = []): array
{
    return [
        'type' => 'skema data spasial',
        'id' => $id,
        'attributes' => [
            'sektor' => ['id' => $sektorId, 'kode' => $sektorKode, 'nama' => $sektorNama],
            'versi_skema' => [
                'id' => (int) $id,
                'sektor_id' => $sektorId,
                'nomor_versi' => $versi,
                'status' => $status,
                'catatan_perubahan' => 'Draf uji.',
                'diterbitkan_pada' => $status === 'terbit' ? '2025-10-01T09:00:00+07:00' : null,
                'diterbitkan_oleh' => null,
            ],
            'isian' => $isian,
        ],
    ];
}

/**
 * @param  array<int, array<string, mixed>>  $items
 * @return array<string, mixed>
 */
function collection(array $items, int $total = 0): array
{
    return [
        'data' => $items,
        'meta' => ['pagination' => [
            'total' => $total ?: count($items),
            'count' => count($items),
            'per_page' => 200,
            'current_page' => 1,
            'total_pages' => 1,
        ]],
        'links' => [],
    ];
}

/**
 * Palsukan daftar sektor + riwayat versi + versi aktif.
 *
 * ⚠️ Q9: daftar skema diambil dari `/skema/riwayat`, sedangkan isian dari
 * `/skema?sektor=`. Keduanya harus dipalsukan terpisah.
 *
 * @param  array<int, array<string, mixed>>  $sektor
 * @param  array<int, array<string, mixed>>  $riwayat
 */
function fakeSektorDanSkema(array $sektor, array $riwayat = [], array $skemaAktif = []): void
{
    Http::fake([
        '*/api/v1/config/kabupaten*' => Http::response(['data' => []], 200),
        '*/api/v1/config/kecamatan*' => Http::response(['data' => []], 200),
        '*/api/v1/config/desa*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(collection($sektor), 200),
        // Q9: daftar seluruh versi. Callback menyaring berdasarkan
        // `sektor_id` supaya "hanya versi sektor terpilih yang tampil"
        // benar-benar teruji (bukan sekadar satu daftar global).
        '*/api/v1/dataspasial/skema/riwayat*' => function (Request $request) use ($sektor, $riwayat) {
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);
            $kode = (string) ($query['sektor'] ?? '');

            $idSektor = null;
            foreach ($sektor as $s) {
                if (($s['attributes']['kode'] ?? null) === $kode) {
                    $idSektor = (int) $s['id'];
                    break;
                }
            }

            $terfilter = $riwayat === [] ? [] : array_values(array_filter(
                $riwayat,
                fn (array $r): bool => $idSektor === null
                    || (int) ($r['attributes']['sektor_id'] ?? 0) === $idSektor
            ));

            return Http::response(collection($terfilter), 200);
        },

        // Versi aktif — isian diambil dari sini.
        '*/api/v1/dataspasial/skema?*' => Http::response(
            $skemaAktif === [] ? collection([]) : $skemaAktif, 200
        ),
        '*/api/v1/dataspasial/skema/*' => Http::response($skemaAktif, 200),
        '*' => Http::response(collection([]), 200),
    ]);
}

/**
 * SALINAN respons nyata `GET /dataspasial/skema/riwayat?sektor=SK0`
 * (Q9). Endpoint ini berbeda bentuk dari `skema data spasial`:
 * datar, tanpa `sektor`, dan tiap entri memuat `nomor_versi` langsung.
 *
 * @return array<string, mixed>
 */
function riwayatNyata(int $sektorId = 4234): array
{
    return [
        'data' => [
            [
                'type' => 'riwayat versi skema',
                'id' => '8143',
                'attributes' => [
                    'sektor_id' => $sektorId,
                    'nomor_versi' => 3,
                    'status' => 'terbit',
                    'catatan_perubahan' => 'Versi terbit',
                    'diterbitkan_pada' => '2025-10-05T14:00:00.000000Z',
                    'diterbitkan_oleh' => 'Budi Santoso',
                ],
            ],
            [
                'type' => 'riwayat versi skema',
                'id' => '8142',
                'attributes' => [
                    'sektor_id' => $sektorId,
                    'nomor_versi' => 1,
                    'status' => 'draf',
                    'catatan_perubahan' => 'Versi draf awal',
                    'diterbitkan_pada' => null,
                    'diterbitkan_oleh' => null,
                ],
            ],
        ],
    ];
}

// ---------------------------------------------------------------------------
// US1#1 — daftar sektor
// ---------------------------------------------------------------------------

it('menampilkan daftar sektor beserta label versi atau draf', function () {
    fakeSektorDanSkema(
        [itemSektor('1', 'pendidikan', 'Pendidikan'), itemSektor('2', 'kesehatan', 'Kesehatan')],
        [itemRiwayat('9', 1, 3, 'draf'), itemRiwayat('5', 1, 2, 'terbit')]
    );

    $this->get(route('dataspasial.skema'))
        ->assertOk()
        ->assertSee('Pendidikan')
        ->assertSee('Kesehatan')
        ->assertSee('Kode sektor');
});

it('menampilkan keadaan kosong saat DataSpasial belum memiliki sektor', function () {
    fakeSektorDanSkema([]);

    $this->get(route('dataspasial.skema'))
        ->assertOk()
        ->assertSee('Belum ada data')
        ->assertDontSee('Data sementara tidak tersedia');
});

// ---------------------------------------------------------------------------
// FR-007a-1 / SC-013 (spec Q7) — kunjungan pertama HANYA memanggil daftar sektor
// ---------------------------------------------------------------------------

it('hanya memanggil daftar sektor pada kunjungan pertama', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(collection([
            itemSektor('1', 'pendidikan', 'Pendidikan'),
            itemSektor('2', 'kesehatan', 'Kesehatan'),
        ]), 200),
        '*/api/v1/dataspasial/*' => Http::response(collection([
            itemRiwayat('9', 1, 3, 'draf'),
        ]), 200),
    ]);

    $this->get(route('dataspasial.skema'))->assertOk();

    // Tepat satu pemanggilan baca DataSpasial: daftar sektor.
    Http::assertSentCount(2); // 1 sektor + 1 config/sidebar yang di-fake terpisah

    $calls = Http::recorded()->map(fn ($pair) => [
        'method' => $pair[0]->method(),
        'path' => parse_url($pair[0]->url(), PHP_URL_PATH),
    ]);

    $callsDataSpasial = $calls->filter(fn ($call) => str_contains($call['path'], '/dataspasial/'))->values();

    expect($callsDataSpasial)->toHaveCount(1)
        ->and($callsDataSpasial[0]['path'])->toContain('/dataspasial/sektor');
});

it('tidak pernah memanggil daftar skema maupun detail skema tanpa sektor terpilih', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/*' => Http::response(collection([
            itemSektor('1', 'pendidikan', 'Pendidikan'),
            itemRiwayat('9', 1, 3, 'draf'),
        ]), 200),
    ]);

    $this->get(route('dataspasial.skema'))->assertOk();

    // Endpoint ini membutuhkan sektor dan DIPASTIKAN GAGAL bila dipanggil
    // tanpa parameter — jadi tidak boleh terpanggil sama sekali.
    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/dataspasial/skema'));
});

// ---------------------------------------------------------------------------
// SC-014 — setelah sektor dipilih, tepat SATU pemanggilan skema dengan parameter
// sektor, dan endpoint riwayat versi TIDAK pernah dipanggil (Q22).
// ---------------------------------------------------------------------------

it('memuat isian skema terakhir lewat satu pemanggilan skema, tanpa riwayat versi', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(collection([
            itemSektor('1', 'pendidikan', 'Pendidikan'),
        ]), 200),
        '*/api/v1/dataspasial/skema?sektor=1' => Http::response(
            collection([
                itemSkemaAktif('10', 'pendidikan', 'Pendidikan', 1, 3, 'draf', [
                    ['kode' => 'jumlah_siswa', 'label' => 'Jumlah Siswa', 'tipe' => 'angka', 'wajib' => true,
                        'satuan' => 'orang', 'opsi' => null, 'urutan' => 1,
                        'tampil_popup' => true, 'tampil_filter' => false, 'tampil_indikator' => false,
                        'pemetaan' => []],
                ]),
            ]),
            200,
        ),
        '*' => Http::response(collection([]), 200),
    ]);

    $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']))->assertOk();

    $calls = Http::recorded()->map(fn ($pair) => [
        'method' => $pair[0]->method(),
        'path' => parse_url($pair[0]->url(), PHP_URL_PATH),
    ])->filter(fn ($call) => str_contains($call['path'], '/dataspasial/'));

    // Tepat satu pemanggilan ke /skema dengan parameter sektor (SC-014).
    $skemaCalls = $calls->filter(fn ($call) => str_contains($call['path'], '/dataspasial/skema') && ! str_contains($call['path'], '/skema/riwayat'));

    expect($skemaCalls)->toHaveCount(1);
    expect($skemaCalls->first()['path'])->toContain('/dataspasial/skema');

    // Endpoint riwayat versi TIDAK boleh disentuh (Q22).
    expect($calls->filter(fn ($call) => str_contains($call['path'], '/skema/riwayat')))->toHaveCount(0);
});

// ---------------------------------------------------------------------------
// US1#6 — sumber data bermasalah (FR-005)
// ---------------------------------------------------------------------------

it('menampilkan pesan data sementara tidak tersedia saat sumber bermasalah', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*' => Http::response(['message' => 'Modul DataSpasial belum terpasang'], 503),
    ]);

    $this->get(route('dataspasial.skema'))
        ->assertOk()
        ->assertSee('Data sementara tidak tersedia')
        ->assertDontSee('Stack trace');
});

it('menyembunyikan seluruh editor termasuk tombol simpan ketika sumber tidak tersedia', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*' => Http::response(['message' => 'Modul DataSpasial belum terpasang'], 503),
    ]);

    $content = $this->get(route('dataspasial.skema'))->assertOk()->getContent();

    // Komponen `<x-dataspasial-status>` mengganti SELURUH area editor dengan
    // pesan (R10). Karena tidak ada editor yang dirender, tidak mungkin ada
    // tombol simpan yang aktif — inilah bentuk "nonaktif" yang dimaksud FR-005.
    expect($content)
        ->toContain('Data sementara tidak tersedia')
        ->not->toContain('data-status-editor=')
        ->not->toContain('id="btn-simpan-draf"');
});

// ---------------------------------------------------------------------------
// FR-041 — tombol Terbitan AKTIF saat skema berstatus draf (Q11)
//
// ⚠️ Bertentangan dengan behaviour lama FR-040/Q1 yang menyebut KAB-03.
// Frontend masih menjaga dirty-guard FR-045 saat ada perubahan belum disimpan.
// ---------------------------------------------------------------------------

it('menampilkan tombol Terbitan dalam keadaan aktif untuk draf dan tanpa catatan KAB-03', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(collection([
            itemSektor('1', 'pendidikan', 'Pendidikan'),
        ]), 200),
        '*/api/v1/dataspasial/skema?sektor=pendidikan' => Http::response(collection([
            itemSkemaAktif('10', 'pendidikan', 'Pendidikan', 1, 3, 'draf', [
                ['kode' => 'lebar_jalan', 'label' => 'Lebar Jalan', 'tipe' => 'angka', 'wajib' => true,
                    'satuan' => 'meter', 'opsi' => null, 'urutan' => 1,
                    'tampil_popup' => false, 'tampil_filter' => false, 'tampil_indikator' => false,
                    'pemetaan' => []],
            ]),
        ]), 200),
        '*' => Http::response(collection([]), 200),
    ]);

    $content = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']))->assertOk()->getContent();

    expect($content)
        ->toContain('id="btn-terbitan"')
        ->not->toContain('KAB-03');

    preg_match('/<button[^>]*id="btn-terbitan"[^>]*>/', $content, $m);
    expect($m)->not->toBeEmpty();
    expect($m[0])->not->toContain('disabled');
});

it('menampilkan tombol Terbitan nonaktif ketika skema sudah terbit', function () {
    fakeSektorDanSkema(
        [itemSektor('1', 'pendidikan', 'Pendidikan')],
        [itemRiwayat('9', 1, 3, 'terbit')]
    );

    $content = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']))->assertOk()->getContent();

    preg_match('/<button[^>]*id="btn-terbitan"[^>]*>/', $content, $m);
    expect($m)->not->toBeEmpty();
    expect($m[0])->toContain('disabled');
});

it('tidak pernah memanggil endpoint terbitan dari halaman editor', function () {
    fakeSektorDanSkema(
        [itemSektor('1', 'pendidikan', 'Pendidikan')],
        [itemRiwayat('9', 1, 3, 'draf')]
    );

    $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan', 'skema' => 9]))->assertOk();

    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/terbit'));
});

it('tidak memanggil riwayat versi ketika belum ada sektor terpilih', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(collection([
            itemSektor('1', 'pendidikan', 'Pendidikan'),
        ]), 200),
        '*' => Http::response(collection([]), 200),
    ]);

    $this->get(route('dataspasial.skema'))->assertOk();

    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/skema/riwayat'));
});

// ---------------------------------------------------------------------------
// Q8 — Kontrak terverifikasi langsung ke API NYATA (2026-10-15)
//
// Fixture di bawah adalah SALINAN PERSIS dari respons instance API Satu Data
// yang hidup (`GET /dataspasial/sektor` dan `GET /dataspasial/skema?sektor=4234`).
// Test ini mengunci discoveries yang tidak ada pada draft openapi-fondasi.yaml:
//   K1 param `sektor` (bukan `sektor_id`)
//   K2 tidak ada endpoint detail; isian ikut di respons skema
//   K3 versi/status di `attributes.versi_skema`
//   K4 nama resmi penanda: `tampil_popup` / `tampil_filter` / `tampil_indikator`
// ---------------------------------------------------------------------------

it('memetakan respons API nyata beserta penanda tampil yang resmi', function () {
    // SALINAN respons nyata.
    $respons = [
        'type' => 'skema data spasial',
        'id' => '8143',
        'attributes' => [
            'sektor' => ['id' => 4234, 'kode' => 'SK0', 'nama' => 'Sektor 0'],
            'versi_skema' => [
                'id' => 8143,
                'sektor_id' => 4234,
                'nomor_versi' => 3,
                'status' => 'terbit',
                'catatan_perubahan' => 'Versi terbit',
                'diterbitkan_pada' => '2025-10-05T14:00:00.000000Z',
                'diterbitkan_oleh' => 'Budi Santoso',
            ],
            'isian' => [[
                'id' => 8484,
                'kode' => 'lebar_jalan',
                'label' => 'Lebar Jalan',
                'tipe' => 'angka',
                'wajib' => true,
                'satuan' => 'meter',
                'opsi' => null,
                'aturan_validasi' => null,
                'deskripsi' => null,
                'urutan' => 1,
                'diarsipkan' => false,
                'tampil_popup' => true,
                'tampil_filter' => false,
                'tampil_indikator' => false,
                'pemetaan' => [[
                    'id' => 3951,
                    'sumber' => 'openSID',
                    'rujukan_sumber' => 'tbl_jalan.lebar',
                    'aturan_hitung' => null,
                ]],
            ]],
        ],
    ];

    $editor = app(SkemaEditorService::class);

    // K3: versi & status dibaca dari `versi_skema`.
    $skema = $editor->petakanSektorSkema($respons);

    expect($skema['id'])->toBe(8143)
        ->and($skema['sektorId'])->toBe(4234)
        ->and($skema['sektorKode'])->toBe('SK0')
        ->and($skema['nomorVersi'])->toBe(3)
        ->and($skema['status'])->toBe('terbit')
        ->and($skema['jumlahIsian'])->toBe(1);

    $isian = $editor->petakanDaftarIsian($respons['attributes']['isian']);

    // K4: nama resmi `tampil_*`, bukan asumsi `popup_peta`/`filter`/`indikator`.
    expect($isian[0]['popupPeta'])->toBeTrue()
        ->and($isian[0]['sebagaiFilter'])->toBeFalse()
        ->and($isian[0]['sebagaiIndikator'])->toBeFalse();

    // Pemetaan tertanam: `sumber` adalah enum API (`openSID`), bukan kode sektor.
    expect($isian[0]['sumber'])->toBe('openSID')
        ->and($isian[0]['rujukan'])->toBe('tbl_jalan.lebar');
});

it('memetakan counter sektor nyata untuk label versi dan draf', function () {
    // SALINAN respons nyata `GET /dataspasial/sektor`.
    $responsSektor = [[
        'type' => 'daftar sektor',
        'id' => '4234',
        'attributes' => [
            'kode' => 'SK0',
            'nama' => 'Sektor 0',
            'ikon' => 'fa-cube',
            'deskripsi' => 'Untuk unit test',
            'urutan' => 0,
            'status' => 'aktif',
            'jumlah_versi_skema' => 2,
            'jumlah_versi_terbit' => 1,
        ],
    ]];

    $editor = app(SkemaEditorService::class);
    $daftar = $editor->petakanDaftarSektor($responsSektor);

    // 2 versi, 1 terbit → versi terakhir (2) masih berupa draf sehingga
    // `adaDraf` benar dan label menampilkan "Versi 2 · Draf".
    expect($daftar[0]['jumlahSkema'])->toBe(2)
        ->and($daftar[0]['jumlahTerbit'])->toBe(1)
        ->and($daftar[0]['adaDraf'])->toBeTrue()
        ->and($daftar[0]['nomorVersiTerakhir'])->toBe(2)
        ->and($daftar[0]['statusTerakhir'])->toBe('draf');
});

it('menyusun badan draf memakai nama field resmi API', function () {
    $editor = app(SkemaEditorService::class);

    $payload = $editor->composeDrafPayload([[
        'kode' => 'lebar_jalan',
        'label' => 'Lebar Jalan',
        'tipe' => 'angka',
        'urutan' => 1,
        'popupPeta' => true,
        'sebagaiFilter' => false,
        'sebagaiIndikator' => true,
        'sumber' => 'openSID',
        'tabelSumber' => 'tbl_jalan',
        'kolomSumber' => 'lebar',
        'rujukan' => 'tbl_jalan.lebar',
    ]], ['sektor' => 'SK0', 'sektor_nama' => 'Sektor 0', 'keterangan' => 'Draf uji']);

    expect($payload['sektor'])->toBe('SK0')
        ->and($payload['sektor_nama'])->toBe('Sektor 0')
        ->and($payload['keterangan'])->toBe('Draf uji')
        ->and(array_key_exists('nomor_versi', $payload))->toBeFalse()
        ->and($payload['isian'][0]['tampil_di_popup'])->toBeTrue()
        ->and($payload['isian'][0]['tampil_di_filter'])->toBeFalse()
        ->and($payload['isian'][0]['tampil_di_indikator'])->toBeTrue()
        // Pemetaan dikirim sebagai ARRAY di dalam isian (bentuk nyata §6a.3 / Q20).
        // ⚠️ `ds_pemetaan` hanya punya SATU kolom `rujukan_sumber` "tabel.kolom"
        // — bukan `tabel_sumber`/`kolom_sumber` terpisah (§6a.4).
        ->and($payload['isian'][0]['pemetaan'])->toBe([[
            'sumber' => 'openSID',
            'rujukan_sumber' => 'tbl_jalan.lebar',
        ]]);
});

it('tidak mengirim pemetaan untuk isian yang diisi manual', function () {
    $editor = app(SkemaEditorService::class);

    $payload = $editor->composeDrafPayload([
        ['kode' => 'a', 'label' => 'A', 'tipe' => 'teks', 'urutan' => 1, 'sumber' => 'manual'],
    ], ['sektor' => 'SK0']);

    expect($payload['isian'][0]['pemetaan'])->toBeNull();
});

// ---------------------------------------------------------------------------
// Q24/Q25 — FR-008c/d/e & SC-021/022/023 — tombol "Ubah" & "Batal"
//
// FR-008c — tombol "Ubah" hanya tampil saat skema berstatus `terbit`.
// FR-008d — aturan draf tetap berlaku pada draf hasil "Ubah".
// FR-008e — tombol "Batal" hanya tampil pada keadaan menyunting hasil "Ubah".
// ---------------------------------------------------------------------------

it('menampilkan tombol Ubah hanya ketika skema berstatus terbit', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(collection([
            itemSektor('1', 'pendidikan', 'Pendidikan'),
        ]), 200),
        '*/api/v1/dataspasial/skema?sektor=pendidikan' => Http::response(
            collection([itemSkemaAktif('10', 'pendidikan', 'Pendidikan', 1, 2, 'terbit', [
                ['kode' => 'lebar_jalan', 'label' => 'Lebar Jalan', 'tipe' => 'angka', 'wajib' => true,
                    'satuan' => 'meter', 'opsi' => null, 'urutan' => 1,
                    'tampil_popup' => false, 'tampil_filter' => false, 'tampil_indikator' => false,
                    'pemetaan' => []],
            ])]),
            200
        ),
        '*' => Http::response(collection([]), 200),
    ]);

    $content = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']))
        ->assertOk()
        ->getContent();

    // FR-008c — tombol Ubah ada dan TIDAK memakai `d-none` pada status terbit.
    expect($content)
        ->toContain('id="btn-ubah"')
        ->toContain('data-status-editor="terbit"');

    preg_match('/<button[^>]*id="btn-ubah"[^>]*>/', $content, $ubah);
    expect($ubah)->not->toBeEmpty();
    expect($ubah[0])->not->toContain('d-none');

    // FR-008e — tombol Batal TERSEMBUNYI pada keadaan awal.
    preg_match('/<button[^>]*id="btn-batal-ubah"[^>]*>/', $content, $batal);
    expect($batal)->not->toBeEmpty();
    expect($batal[0])->toContain('d-none');
});

it('menyembunyikan tombol Ubah ketika skema yang dimuat berstatus draf', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(collection([
            itemSektor('1', 'pendidikan', 'Pendidikan'),
        ]), 200),
        '*/api/v1/dataspasial/skema?sektor=pendidikan' => Http::response(
            collection([itemSkemaAktif('11', 'pendidikan', 'Pendidikan', 1, 3, 'draf', [
                ['kode' => 'lebar_jalan', 'label' => 'Lebar Jalan', 'tipe' => 'angka', 'wajib' => true,
                    'satuan' => 'meter', 'opsi' => null, 'urutan' => 1,
                    'tampil_popup' => false, 'tampil_filter' => false, 'tampil_indikator' => false,
                    'pemetaan' => []],
            ])]),
            200
        ),
        '*' => Http::response(collection([]), 200),
    ]);

    $content = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']))
        ->assertOk()
        ->getContent();

    // FR-008c — draf sudah dapat disunting, jadi tombol Ubah TIDAK dirender.
    expect($content)
        ->toContain('data-status-editor="draf"')
        ->not->toContain('id="btn-ubah"');
});

it('menampilkan kolom nama sektor yang dapat disunting pada editor', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(collection([
            itemSektor('1', 'pendidikan', 'Pendidikan Dasar'),
        ]), 200),
        '*/api/v1/dataspasial/skema?sektor=pendidikan' => Http::response(
            collection([itemSkemaAktif('12', 'pendidikan', 'Pendidikan Dasar', 1, 3, 'draf', [
                ['kode' => 'lebar_jalan', 'label' => 'Lebar Jalan', 'tipe' => 'angka', 'wajib' => true,
                    'satuan' => 'meter', 'opsi' => null, 'urutan' => 1,
                    'tampil_popup' => false, 'tampil_filter' => false, 'tampil_indikator' => false,
                    'pemetaan' => []],
            ])]),
            200
        ),
        '*' => Http::response(collection([]), 200),
    ]);

    $content = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']))
        ->assertOk()
        ->getContent();

    // US7/Q19 — nama sektor dapat disunting dari editor dan terkirim sebagai
    // `sektor_nama`; kode sektor tetap baca-saja (FR-007d-5).
    expect($content)
        ->toContain('id="input-sektor-nama-editor"')
        ->toContain('Pendidikan Dasar');
});

// ---------------------------------------------------------------------------
// Q26 — FR-008f / SC-024 — "Tambah sektor baru" memakai editor yang sama
// ---------------------------------------------------------------------------

it('tidak lagi merender formulir penambahan sektor terpisah', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(collection([
            itemSektor('1', 'pendidikan', 'Pendidikan'),
        ]), 200),
        '*' => Http::response(collection([]), 200),
    ]);

    $content = $this->get(route('dataspasial.skema'))
        ->assertOk()
        ->getContent();

    // FR-008f/Q26 — form terpisah dihapus; tombolnya tetap ada.
    expect($content)
        ->not->toContain('form-tambah-sektor')
        ->toContain('id="btn-tambah-sektor"');
});

// ---------------------------------------------------------------------------
// US7 / FR-007d-4/5/9 — pemutakhiran nama sektor dari editor (Q19/SC-015)
// ---------------------------------------------------------------------------

it('mengirim sektor_nama terbaru dan menjaga kode sektor tidak berubah', function () {
    Http::fake([
        '*/api/v1/dataspasial/skema' => Http::response([
            'success' => true,
            'data' => ['type' => 'ds_skema_versi', 'id' => '9'],
        ], 201),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->postJson(route('dataspasial.skema.draf.store'), [
        'sektor' => 'pendidikan',
        'sektor_nama' => 'Pendidikan Dasar',
        'isian' => [[
            'kode' => 'jumlah_siswa',
            'label' => 'Jumlah Siswa',
            'tipe' => 'angka',
            'wajib' => true,
            'urutan' => 1,
            'popupPeta' => false,
            'sebagaiFilter' => false,
            'sebagaiIndikator' => false,
        ]],
    ])->assertOk()->assertJsonPath('success', true);

    Http::assertSent(function ($request) {
        $body = json_decode((string) $request->body(), true);

        return str_ends_with($request->url(), '/api/v1/dataspasial/skema')
            && ($body['sektor'] ?? null) === 'pendidikan'
            && ($body['sektor_nama'] ?? null) === 'Pendidikan Dasar';
    });
});

it('menolak nama sektor kosong sebelum permintaan dikirim', function () {
    // FR-007f — nama kosong ditolak di layar, tanpa request.
    Http::fake();

    $this->postJson(route('dataspasial.skema.draf.store'), [
        'sektor' => 'pendidikan',
        'sektor_nama' => '   ',
        'isian' => [[
            'kode' => 'jumlah_siswa',
            'label' => 'Jumlah Siswa',
            'tipe' => 'angka',
            'urutan' => 1,
        ]],
    ])->assertStatus(422)->assertJsonValidationErrors('sektor_nama');

    Http::assertNothingSent();
});

it('menampilkan pesan generik dan menjaga isi editor saat nama sektor bentrok', function () {
    Http::fake([
        '*/api/v1/dataspasial/skema' => Http::response([
            'success' => false,
            'message' => 'Validasi gagal.',
            'errors' => ['sektor_nama' => ['Nama sektor sudah dipakai sektor lain.']],
        ], 422),
        '*' => Http::response(['data' => []], 200),
    ]);

    // FR-007f/Q13 — keunikan nama adalah tanggung jawab API; pesan teknis tidak
    // dibocorkan ke pengguna (FR-038) dan isi editor tetap utuh (FR-041b).
    $this->postJson(route('dataspasial.skema.draf.store'), [
        'sektor' => 'pendidikan',
        'sektor_nama' => 'Kesehatan',
        'isian' => [[
            'kode' => 'jumlah_siswa',
            'label' => 'Jumlah Siswa',
            'tipe' => 'angka',
            'urutan' => 1,
        ]],
    ])->assertStatus(503)->assertJsonPath('success', false);
});

// ---------------------------------------------------------------------------
// Label versi terakhir + status pada daftar sektor (FR-007, Q22/SC-013)
// ---------------------------------------------------------------------------

/**
 * Ambil teks badge status versi terakhir dari HTML panel sektor.
 */
function badgeStatusSektor(string $content): ?string
{
    return preg_match('/data-testid="label-status-versi-sektor">([^<]*)</', $content, $m) === 1
        ? trim($m[1])
        : null;
}

/**
 * Ambil nomor versi terakhir dari badge `v{n}` (null bila belum ada skema).
 */
function badgeNomorVersiSektor(string $content): ?string
{
    return preg_match('/data-testid="label-nomor-versi-sektor">\s*v(\d+)/', $content, $m) === 1
        ? $m[1]
        : null;
}

it('menampilkan nomor versi terakhir dan status draf pada daftar sektor', function () {
    // 3 versi, hanya 1 yang terbit → versi terakhir (3) masih berupa draf.
    fakeEndpointEditor([fxSektor('1', 'pendidikan', 'Pendidikan', 3, 1)]);

    $content = $this->get(route('dataspasial.skema'))->assertOk()->getContent();

    expect(badgeStatusSektor($content))->toBe('Draf')
        ->and(badgeNomorVersiSektor($content))->toBe('3');
});

it('menampilkan nomor versi terakhir dan status terbit pada daftar sektor', function () {
    // 3 versi, semuanya terbit → versi terakhir (3) berstatus terbit.
    fakeEndpointEditor([fxSektor('1', 'pendidikan', 'Pendidikan', 3, 3)]);

    $content = $this->get(route('dataspasial.skema'))->assertOk()->getContent();

    expect(badgeStatusSektor($content))->toBe('Terbit')
        ->and(badgeNomorVersiSektor($content))->toBe('3');
});

it('menampilkan belum ada skema tanpa nomor versi pada daftar sektor', function () {
    fakeEndpointEditor([fxSektor('1', 'pendidikan', 'Pendidikan', 0, 0)]);

    $content = $this->get(route('dataspasial.skema'))->assertOk()->getContent();

    expect(badgeStatusSektor($content))->toBe('Belum ada skema')
        ->and(badgeNomorVersiSektor($content))->toBeNull();
});

it('tidak menampilkan jumlah versi pada daftar sektor', function () {
    fakeEndpointEditor([fxSektor('1', 'pendidikan', 'Pendidikan', 3, 3)]);

    $content = $this->get(route('dataspasial.skema'))->assertOk()->getContent();

    // ⚠️ Informasi "3 versi" dihapus; yang tampil hanya nomor versi terakhir (v3).
    expect($content)->not->toContain(' versi<')
        ->and(badgeNomorVersiSektor($content))->toBe('3');
});
