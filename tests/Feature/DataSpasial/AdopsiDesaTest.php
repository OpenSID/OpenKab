<?php

declare(strict_types=1);

use App\Models\Setting;
use App\Services\AdopsiDesaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

uses(DatabaseTransactions::class);

/**
 * US4 — Tabel adopsi desa di dalam layar terbitan (FR-047, FR-060 … FR-071).
 *
 * ⚠️ Kontrak nyata (contracts §5.2): field `nomor_versi_skema`, `status`
 * (`memakai_skema`/`belum_ada_data`), `terakhir_ada_data`; tidak ada data
 * kecamatan (Utang U7) dan paginasi API konyektif (Utang U9).
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

/** Stub adopsi + sektor (versi terbit terbaru = v3). */
function fakeAdopsi(?array $baris = null, int $sektorId = 4234, ?int $versiTerbaru = 3): void
{
    Http::fake([
        '*/api/v1/dataspasial/adopsi*' => Http::response(fxAdopsiKoleksi($baris), 200),
        '*/api/v1/dataspasial/skema/riwayat*' => Http::response(fakeKoleksi([
            fxRiwayatVersi('8143', $sektorId, $versiTerbaru ?? 3, 'terbit'),
        ]), 200),
        '*/api/v1/dataspasial/skema?*' => Http::response(fxSkemaVersiDenganArsip(), 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(fakeKoleksi([fxSektor('4234', 'SK0', 'Sektor 0')]), 200),
        '*/api/v1/config/*' => Http::response(fakeConfigEndpoint(), 200),
        '*' => Http::response(['data' => []], 200),
    ]);
}

// -----------------------------------------------------------------------
// FR-060/FR-061 — baris & status
// -----------------------------------------------------------------------

it('menampilkan baris sektor terpilih dan desa yang belum pernah menarik skema', function () {
    fakeAdopsi();

    $response = $this->getJson(route('dataspasial.skema.adopsi', ['sektor' => 'SK0']));

    $response->assertOk();
    $response->assertJsonPath('success', true);
    $response->assertJsonPath('versi_terbaru', 3);

    // Sektor 4234 (Bajera) + 131 baris tanpa sektor_id (belum adopsi) ikut
    // (Q4 -> A). Bantas (sektor 4235) tidak ikut.
    expect($response->json('ringkasan.total_desa'))->toBe(132);
    expect($response->json('ringkasan.menunggu_adopsi'))->toBe(131);

    // Halaman pertama sudah memuat Bajera (Terpusat).
    expect(array_column($response->json('baris'), 'nama_desa'))->toContain('Bajera');

    // Desa yang belum menarik skema tetap bisa dicari (bukan tersaring diam-diam).
    // Pencarian bersifat substring, jadi 'Desa 133' dipilih karena unik.
    $cari = $this->getJson(route('dataspasial.skema.adopsi', ['sektor' => 'SK0', 'q' => 'Desa 133']));
    expect($cari->json('ringkasan.total_desa'))->toBe(1);
    expect($cari->json('baris.0.status_label'))->toBe('Menunggu adopsi');
    expect($cari->json('baris.0.nomor_versi_label'))->toBe('—');

    // Desa sektor lain tidak pernah muncul.
    $semua = $this->getJson(route('dataspasial.skema.adopsi', ['sektor' => 'SK0', 'q' => 'Bantas']));
    expect($semua->json('ringkasan.total_desa'))->toBe(0);
});

it('menurunkan status tertinggal dari nomor versi terpakai', function () {
    fakeAdopsi([
        fxAdopsiBaris(1, '5102012001', 'Bajera', 3, 4234),   // versi terbaru -> Terpusat
        fxAdopsiBaris(2, '5102022003', 'Bantas', 1, 4234),    // versi lama -> Tertinggal
        fxAdopsiBaris(3, '5102022004', 'Antapan', null, null, 'belum_ada_data', null),
    ]);

    $baris = collect($this->getJson(route('dataspasial.skema.adopsi', ['sektor' => 'SK0']))->json('baris'))
        ->keyBy('nama_desa');

    expect($baris['Bajera']['status_label'])->toBe('Terpusat');
    expect($baris['Bajera']['tandai_tertinggal'])->toBeFalse();
    expect($baris['Bantas']['status_label'])->toBe('Tertinggal');
    expect($baris['Bantas']['tandai_tertinggal'])->toBeTrue();
    expect($baris['Antapan']['status_label'])->toBe('Menunggu adopsi');
    expect($baris['Antapan']['nomor_versi_label'])->toBe('—');
    expect($baris['Antapan']['terakhir_ada_data_label'])->toBe('—');
});

it('tidak mengklaim tertingale untuk desa dengan versi lebih baru dari versi terbit', function () {
    fakeAdopsi([fxAdopsiBaris(1, '5102012001', 'Bajera', 9, 4234)]);

    $baris = $this->getJson(route('dataspasial.skema.adopsi', ['sektor' => 'SK0']))->json('baris.0');

    expect($baris['status_label'])->toBe('Terpusat');
    expect($baris['tandai_tertinggal'])->toBeFalse();
});

it('menampilkan tanda hubung dan mencatat log untuk status yang tidak dikenal', function () {
    Log::spy();

    fakeAdopsi([fxAdopsiBaris(1, '5102012001', 'Bajera', 3, 4234, 'entah')]);

    $baris = $this->getJson(route('dataspasial.skema.adopsi', ['sektor' => 'SK0']))->json('baris.0');

    expect($baris['status_label'])->toBe('—');
    expect($baris['tandai_tertinggal'])->toBeFalse();

    Log::shouldHaveReceived('warning')
        ->withArgs(fn (string $p, array $c = []): bool => str_contains($p, 'status adopsi tidak dikenal'))
        ->atLeast()->once();
});

// -----------------------------------------------------------------------
// FR-062/FR-063/FR-070 — ringkasan, saringan, urutan
// -----------------------------------------------------------------------

it('menyaring berdasarkan status dan kata kunci nama desa', function () {
    fakeAdopsi([
        fxAdopsiBaris(1, '5102012001', 'Bajera', 3, 4234),
        fxAdopsiBaris(2, '5102022003', 'Bantas', 1, 4234),
        fxAdopsiBaris(3, '5102022004', 'Antapan', null, null, 'belum_ada_data', null),
    ]);

    $tertinggal = $this->getJson(route('dataspasial.skema.adopsi', [
        'sektor' => 'SK0', 'status' => 'tertinggal',
    ]));

    expect($tertinggal->json('ringkasan.total_desa'))->toBe(1);
    expect($tertinggal->json('ringkasan.tertinggal'))->toBe(1);
    expect($tertinggal->json('baris.0.nama_desa'))->toBe('Bantas');

    $cari = $this->getJson(route('dataspasial.skema.adopsi', ['sektor' => 'SK0', 'q' => 'baj']));
    expect($cari->json('baris.0.nama_desa'))->toBe('Bajera');

    $gabungan = $this->getJson(route('dataspasial.skema.adopsi', [
        'sektor' => 'SK0', 'status' => 'tertinggal', 'q' => 'bantas',
    ]));
    expect($gabungan->json('ringkasan.total_desa'))->toBe(1);
});

it('menampilkan jumlah konflik sebagai tanda hubung karena datanya belum tersedia', function () {
    fakeAdopsi();

    $this->getJson(route('dataspasial.skema.adopsi', ['sektor' => 'SK0']))
        ->assertJsonPath('ringkasan.bermasalah', null);
});

it('mendahulukan desa tertinggal pada urutan bawaan', function () {
    fakeAdopsi([
        fxAdopsiBaris(1, '5102012001', 'Asep', 3, 4234),
        fxAdopsiBaris(2, '5102022003', 'Budi', 1, 4234),
    ]);

    $nama = array_column($this->getJson(route('dataspasial.skema.adopsi', ['sektor' => 'SK0']))->json('baris'), 'nama_desa');

    expect($nama[0])->toBe('Budi');
});

it('hanya menawarkan status yang punya sumber data sebagai pilihan saringan', function () {
    fakeAdopsi();

    $pilihan = array_column($this->getJson(route('dataspasial.skema.adopsi', ['sektor' => 'SK0']))->json('pilihan_status'), 'value');

    expect($pilihan)->toContain('tertinggal');
    expect($pilihan)->toContain('terpusat');
    expect($pilihan)->toContain('menunggu_adopsi');
    expect($pilihan)->not->toContain('tidak_diketahui');
});

// -----------------------------------------------------------------------
// FR-065/SC-012 — paginasi di sisi OpenKab
// -----------------------------------------------------------------------

it('memaginasikan hasil sargan di sisi server untuk 133 desa', function () {
    fakeAdopsi();

    $response = $this->getJson(route('dataspasial.skema.adopsi', ['sektor' => 'SK0']));

    $response->assertOk();
    // 133 baris dari API dikurangi Bantas (sektor lain) -> 132 untuk sektor ini.
    expect($response->json('total'))->toBe(132);
    expect(count($response->json('baris')))->toBeLessThanOrEqual(AdopsiDesaService::PER_HALAMAN);
    expect($response->json('total_halaman'))->toBe(6);
    expect($response->json('total_api'))->toBe(133);
});

it('memakai satu pemanggilan adopsi untuk satu halaman tabel', function () {
    fakeAdopsi();

    $this->getJson(route('dataspasial.skema.adopsi', ['sektor' => 'SK0']))->assertOk();

    $sent = collect(Http::recorded())
        ->filter(fn (array $r): bool => str_contains($r[0]->url(), '/dataspasial/adopsi'));

    expect($sent)->toHaveCount(1);
});

it('menampilkan keadaan kosong tanpa error ketika saringan tidak menghasilkan baris', function () {
    fakeAdopsi();

    $this->getJson(route('dataspasial.skema.adopsi', ['sektor' => 'SK0', 'q' => 'zzz-tidak-ada']))
        ->assertOk()
        ->assertJsonPath('baris', [])
        ->assertJsonPath('ringkasan.total_desa', 0);
});

it('menampilkan data sementara tidak tersedia saat adopsi gagal tanpa merusak halaman', function () {
    Http::fake([
        '*/api/v1/dataspasial/adopsi*' => Http::response(['message' => 'boom'], 500),
        '*/api/v1/dataspasial/sektor*' => Http::response(fakeKoleksi([fxSektor('4234', 'SK0', 'Sektor 0')]), 200),
        '*/api/v1/config/*' => Http::response(fakeConfigEndpoint(), 200),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->getJson(route('dataspasial.skema.adopsi', ['sektor' => 'SK0']))
        ->assertStatus(503)
        ->assertJsonPath('message', 'Data sementara tidak tersedia.')
        ->assertJsonPath('baris', [])
        ->assertJsonPath('ringkasan', null);
});

it('menolak ekspor tanpa sektor terpilih', function () {
    fakeAdopsi();

    $this->getJson(route('dataspasial.skema.adopsi.export'))
        ->assertStatus(422);
});

it('menolak pengguna tanpa izin tulis membuka tabel adopsi', function () {
    fakeAdopsi();

    test()->actingAs(makePenggunaBacaSaja(), 'web');

    $this->getJson(route('dataspasial.skema.adopsi', ['sektor' => 'SK0']))->assertForbidden();
});
