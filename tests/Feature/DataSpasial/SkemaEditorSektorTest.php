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
// US1#2 / #7 — daftar skema hanya setelah sektor dipilih (FR-007a)
// ---------------------------------------------------------------------------

it('tidak menampilkan daftar skema sebelum admin memilih sektor', function () {
    fakeSektorDanSkema(
        [itemSektor('1', 'pendidikan', 'Pendidikan')],
        [itemRiwayat('9', 1, 3, 'draf')]
    );

    $content = $this->get(route('dataspasial.skema'))->assertOk()->getContent();

    // Penanda daftar skema belum boleh muncul saat belum ada sektor terpilih.
    expect($content)->toContain('data-testid="panel-skema-belum-dipilih"');
});

it('menampilkan daftar skema setelah admin memilih satu sektor', function () {
    fakeSektorDanSkema(
        [itemSektor('1', 'pendidikan', 'Pendidikan')],
        [itemRiwayat('9', 1, 3, 'draf'), itemRiwayat('5', 1, 2, 'terbit')]
    );

    $content = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']))->assertOk()->getContent();

    expect($content)
        ->toContain('data-testid="panel-skema"')
        ->toContain('Versi 3')
        ->toContain('Draf berjalan')
        ->toContain('Versi 2')
        ->toContain('Terbit');
});

it('menampilkan daftar skema milik sektor yang dipilih saja', function () {
    fakeSektorDanSkema(
        [itemSektor('1', 'pendidikan', 'Pendidikan'), itemSektor('2', 'kesehatan', 'Kesehatan')],
        [itemRiwayat('9', 1, 3, 'draf'), itemRiwayat('8', 2, 1, 'draf')]
    );

    $kesehatan = $this->get(route('dataspasial.skema', ['sektor' => 'kesehatan']))->assertOk()->getContent();

    expect($kesehatan)
        ->toContain('Versi 1')
        ->not->toContain('Versi 3');
});

// ---------------------------------------------------------------------------
// US1#3 — skema terbit baca-saja (FR-008a)
// ---------------------------------------------------------------------------

it('menampilkan skema terbit dalam keadaan baca-saja dan menonaktifkan simpan', function () {
    fakeSektorDanSkema(
        [itemSektor('1', 'pendidikan', 'Pendidikan')],
        [itemRiwayat('5', 1, 2, 'terbit')]
    );

    $content = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']))->assertOk()->getContent();

    expect($content)
        ->toContain('data-status-editor="terbit"')
        ->toContain('Skema versi terbit hanya dapat dibaca.');
});

it('menampilkan skema draf dalam keadaan dapat disunting', function () {
    fakeSektorDanSkema(
        [itemSektor('1', 'pendidikan', 'Pendidikan')],
        [itemRiwayat('9', 1, 3, 'draf')]
    );

    $content = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']))->assertOk()->getContent();

    expect($content)->toContain('data-status-editor="draf"');
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

it('memanggil daftar skema dengan parameter sektor setelah sektor dipilih', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(collection([
            itemSektor('1', 'pendidikan', 'Pendidikan'),
        ]), 200),
        '*/api/v1/dataspasial/skema*' => Http::response(collection([
            itemRiwayat('9', 1, 3, 'draf'),
        ]), 200),
        '*' => Http::response(collection([]), 200),
    ]);

    $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']))->assertOk();

    Http::assertSent(function ($request) {
        $path = parse_url($request->url(), PHP_URL_PATH);

        if (! str_contains($path, '/dataspasial/skema')) {
            return false;
        }

        // WAJIB menyertakan penyaring sektor dengan NAMA `sektor`.
        // ⚠️ VERIFIKASI LANGSUNG: `sektor_id` membalas 422
        // `{"errors":{"sektor":["Isian sektor wajib diisi."]}}` (kontrak §6a.4 K1).
        parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

        return ($query['sektor'] ?? null) === '1'
            && ! array_key_exists('sektor_id', $query);
    });
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
// US1#12 — validasi nama sektor bentrok (FR-007f)
// ---------------------------------------------------------------------------

it('menolak nama sektor yang kosong tanpa mengirim permintaan', function () {
    fakeSektorDanSkema([itemSektor('1', 'pendidikan', 'Pendidikan')]);

    $this->post(route('dataspasial.skema.sektor.store'), ['nama' => ''])
        ->assertSessionHasErrors('nama');

    Http::assertNotSent(fn ($request) => str_contains($request->url(), '/dataspasial/sektor')
        && $request->method() === 'POST');
});

it('tidak memeriksa keunikan nama sektor di sisi OpenKab dan meneruskan penolakan API', function () {
    // ⚠️ Spec Q13 — keunikan nama sektor adalah tanggung jawab API. OpenKab
    // TIDAK boleh menolak lebih dulu; ia harus mengirim dan menampilkan
    // penjelasan yang dikembalikan API.
    // ⚠️ `Http::fake()` FIFO + awalan method diperlakukan sebagai glob —
    // pemisahan GET/POST dilakukan di dalam closure.
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => function ($request) {
            if ($request->method() === 'POST') {
                return Http::response([
                    'message' => 'Nama sektor sudah digunakan.',
                    'errors' => ['nama' => ['Nama sektor sudah digunakan.']],
                ], 422);
            }

            return Http::response(fakeKoleksi([itemSektor('1', 'pendidikan', 'Pendidikan')]), 200);
        },
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->post(route('dataspasial.skema.sektor.store'), ['nama' => 'Pendidikan'])
        ->assertSessionHas('error');
});

// ⚠️ Q13 — OpenKab tidak boleh mengirim daftar sektor untuk memeriksa keunikan
// sendiri; keunikan sepenuhnya milik API.
it('tidak mengirim nama_sektor_ada ke API saat membuat sektor', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => function ($request) {
            return $request->method() === 'POST'
                ? Http::response(['data' => ['id' => '1', 'attributes' => ['kode' => 'pendidikan']]], 201)
                : Http::response(fakeKoleksi([itemSektor('1', 'pendidikan', 'Pendidikan')]), 200);
        },
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->post(route('dataspasial.skema.sektor.store'), [
        'nama' => 'Pendidikan',
        // Field ini sengaja dikirim untuk membuktikan servers mengabaikannya.
        'nama_sektor_ada' => ['Pendidikan', 'Kesehatan'],
    ])->assertSessionHas('success');

    Http::assertSent(function ($request) {
        if ($request->method() !== 'POST' || ! str_contains($request->url(), '/dataspasial/sektor')) {
            return false;
        }

        return ! array_key_exists('nama_sektor_ada', $request->data())
            && $request['nama'] === 'Pendidikan';
    });
});

it('menolak nama sektor yang hanya berisi spasi', function (string $nama) {
    fakeSektorDanSkema([itemSektor('1', 'pendidikan', 'Pendidikan')]);

    // `TrimStrings` menormalkan spasi tepi menjadi kosong, lalu `required` menolak.
    $this->post(route('dataspasial.skema.sektor.store'), ['nama' => $nama])
        ->assertSessionHasErrors('nama');
})->with(['spasi saja' => '   ', 'spasi dan tab' => "  \t  "]);

it('menerima nama sektor dengan spasi tepi dan menormalkannya', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor' => Http::response([
            'success' => true,
            'data' => ['type' => 'ds_sektor', 'id' => '7', 'attributes' => ['kode' => 'kesehatan', 'nama' => 'Kesehatan']],
        ], 201),
        '*' => Http::response(collection([itemSektor('7', 'kesehatan', 'Kesehatan')]), 200),
    ]);

    $this->post(route('dataspasial.skema.sektor.store'), ['nama' => '  Kesehatan  '])
        ->assertSessionHasNoErrors();

    // Spasi tepi TIDAK ikut dikirim ke API.
    Http::assertSent(function ($request) {
        return $request->method() === 'POST'
            && str_contains($request->url(), '/dataspasial/sektor')
            && ($request->data()['nama'] ?? null) === 'Kesehatan';
    });
});

// ---------------------------------------------------------------------------
// US1#8 — pembuatan sektor (FR-007d-3, U1)
// ---------------------------------------------------------------------------

it('menyimpan sektor baru dan menampilkan kodenya sebagai baca-saja', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor' => Http::response([
            'success' => true,
            'message' => 'Sektor berhasil dibuat.',
            'data' => [
                'type' => 'ds_sektor',
                'id' => '7',
                'attributes' => ['id' => 7, 'kode' => 'kesehatan', 'nama' => 'Kesehatan', 'ikon' => null, 'status' => 'aktif'],
            ],
        ], 201),
        '*' => Http::response(collection([itemSektor('7', 'kesehatan', 'Kesehatan')]), 200),
    ]);

    $this->post(route('dataspasial.skema.sektor.store'), ['nama' => 'Kesehatan'])
        ->assertRedirect()
        ->assertSessionHas('success');
});

it('menampilkan pesan data sementara tidak tersedia bila endpoint sektor belum ada di API', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor' => Http::response(['message' => 'Not Found'], 404),
        '*' => Http::response(collection([itemSektor('1', 'pendidikan', 'Pendidikan')]), 200),
    ]);

    $response = $this->post(route('dataspasial.skema.sektor.store'), ['nama' => 'Kesehatan']);

    $response->assertSessionHas('error');
    expect(session('error'))->toContain('Data sementara tidak tersedia');
});

it('menampilkan pesan yang dapat ditindaklanjuti, bukan halaman error saat pembuatan sektor ditolak API', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor' => Http::response([
            'success' => false,
            'message' => 'Validasi gagal.',
            'errors' => ['nama' => ['nama sudah dipakai sektor lain.']],
        ], 422),
        '*' => Http::response(collection([itemSektor('1', 'pendidikan', 'Pendidikan')]), 200),
    ]);

    $this->post(route('dataspasial.skema.sektor.store'), ['nama' => 'Pendidikan'])
        ->assertRedirect()
        ->assertSessionHas('error');
});

// ---------------------------------------------------------------------------
// FR-001 — kontrol akses (US1 stakeholders)
// ---------------------------------------------------------------------------

it('menolak operator impor dan analis opd pada halaman editor skema', function (string $teamName) {
    fakeSektorDanSkema([itemSektor('1', 'pendidikan', 'Pendidikan')]);

    actingAs($this->{$teamName});

    $this->get(route('dataspasial.skema'))->assertForbidden();
})->with(['operatorImpor', 'analisOpd']);

it('menolak operator impor dan analis opd pada endpoint tulis skema', function (string $teamName) {
    Http::fake();

    actingAs($this->{$teamName});

    $this->post(route('dataspasial.skema.draf.store'), [
        'sektor' => 'pendidikan',
        'isian' => [[
            'kode' => 'jumlah_siswa',
            'label' => 'Jumlah Siswa',
            'tipe' => 'angka',
            'urutan' => 1,
        ]],
    ])->assertForbidden();

    $this->post(route('dataspasial.skema.sektor.store'), ['nama' => 'Kesehatan'])->assertForbidden();
})->with(['operatorImpor', 'analisOpd']);

// ---------------------------------------------------------------------------
// FR-041 — tombol Terbitan AKTIF saat skema berstatus draf (Q11)
//
// ⚠️ Bertentangan dengan behaviour lama FR-040/Q1 yang menyebut KAB-03.
// Frontend masih menjaga dirty-guard FR-045 saat ada perubahan belum disimpan.
// ---------------------------------------------------------------------------

it('menampilkan tombol Terbitan dalam keadaan aktif untuk draf dan tanpa catatan KAB-03', function () {
    fakeSektorDanSkema(
        [itemSektor('1', 'pendidikan', 'Pendidikan')],
        [itemRiwayat('9', 1, 3, 'draf')]
    );

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

// ---------------------------------------------------------------------------
// Q9 — Daftar skema per sektor (endpoint riwayat versi)
// ---------------------------------------------------------------------------

it('mengambil daftar skema dari endpoint riwayat versi setelah sektor dipilih', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(collection([
            itemSektor('1', 'pendidikan', 'Pendidikan'),
        ]), 200),
        '*/api/v1/dataspasial/skema/riwayat*' => Http::response(riwayatNyata(), 200),
        '*' => Http::response(collection([]), 200),
    ]);

    $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']))->assertOk();

    // Q9: daftar WAJIB dari /skema/riwayat dengan KODE sektor.
    Http::assertSent(function ($request) {
        $path = parse_url($request->url(), PHP_URL_PATH);

        if (! str_contains($path, '/dataspasial/skema/riwayat')) {
            return false;
        }

        parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

        return ($query['sektor'] ?? null) === 'pendidikan';
    });
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

it('menampilkan seluruh versi skema sektor pada panel skema', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(['data' => []], 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(collection([
            itemSektor('1', 'pendidikan', 'Pendidikan'),
        ]), 200),
        '*/api/v1/dataspasial/skema/riwayat*' => Http::response(riwayatNyata(), 200),
        '*' => Http::response(collection([]), 200),
    ]);

    $content = $this->get(route('dataspasial.skema', ['sektor' => 'pendidikan']))->assertOk()->getContent();

    // Harus naik: versi 3 (terbit) DAN versi 1 (draf) sama-sama tampil.
    expect($content)
        ->toContain('data-testid="panel-skema"')
        ->toContain('Versi 3')
        ->toContain('Versi 1')
        ->toContain('Terbit')
        ->toContain('Draf berjalan');
});

it('memetakan respons riwayat versi nyata ke daftar skema', function () {
    $editor = app(SkemaEditorService::class);

    $hasil = array_map(
        fn (array $item): array => $editor->petakanRiwayatVersi($item),
        riwayatNyata()['data']
    );

    expect($hasil)->toHaveCount(2)
        ->and($hasil[0]['id'])->toBe(8143)
        ->and($hasil[0]['nomorVersi'])->toBe(3)
        ->and($hasil[0]['status'])->toBe('terbit')
        ->and($hasil[0]['diterbitkanOleh'])->toBe('Budi Santoso')
        ->and($hasil[1]['nomorVersi'])->toBe(1)
        ->and($hasil[1]['status'])->toBe('draf')
        ->and($hasil[1]['diterbitkanPada'])->toBeNull();
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
    $daftar = $editor->petakanDaftarSektor($responsSektor, []);

    expect($daftar[0]['jumlahSkema'])->toBe(2)
        ->and($daftar[0]['jumlahTerbit'])->toBe(1)
        // Sudah ada versi terbit → label "Terbit", bukan "Draf".
        ->and($daftar[0]['adaDraf'])->toBeFalse();
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
        'rujukan' => 'tbl_jalan.lebar',
    ]], ['sektor' => 'SK0']);

    expect($payload['sektor'])->toBe('SK0')
        ->and($payload['isian'][0]['tampil_popup'])->toBeTrue()
        ->and($payload['isian'][0]['tampil_filter'])->toBeFalse()
        ->and($payload['isian'][0]['tampil_indikator'])->toBeTrue()
        // Pemetaan dikirim sebagai ARRAY di dalam isian (bentuk nyata §6a.3).
        ->and($payload['isian'][0]['pemetaan'])->toBe([[
            'id' => 0,
            'sumber' => 'openSID',
            'rujukan_sumber' => 'tbl_jalan.lebar',
            'aturan_hitung' => null,
        ]]);
});

it('tidak mengirim pemetaan untuk isian yang diisi manual', function () {
    $editor = app(SkemaEditorService::class);

    $payload = $editor->composeDrafPayload([
        ['kode' => 'a', 'label' => 'A', 'tipe' => 'teks', 'urutan' => 1, 'sumber' => 'manual'],
    ], ['sektor' => 'SK0']);

    expect($payload['isian'][0]['pemetaan'])->toBeNull();
});
