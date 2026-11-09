<?php

declare(strict_types=1);

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(DatabaseTransactions::class);

/**
 * US4 — Ekspor daftar adopsi (FR-066, FR-067).
 *
 * ⚠️ Berkas ekspor **tidak boleh** memicu permintaan terbitan (FR-047) dan
 * isinya harus sama dengan **seluruh** hasil saringan, bukan hanya halaman aktif.
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

/** @param array<int, array<string, mixed>> $baris */
function fakeEkspor(array $baris): void
{
    Http::fake([
        '*/api/v1/dataspasial/adopsi*' => Http::response(fxAdopsiKoleksi($baris), 200),
        '*/api/v1/dataspasial/skema/riwayat*' => Http::response(fakeKoleksi([fxRiwayatVersi('8143', 4234, 3, 'terbit')]), 200),
        '*/api/v1/dataspasial/skema?*' => Http::response(fxSkemaVersiDenganArsip(), 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(fakeKoleksi([fxSektor('4234', 'SK0', 'Sektor 0')]), 200),
        '*/api/v1/config/*' => Http::response(fakeConfigEndpoint(), 200),
        '*' => Http::response(['data' => []], 200),
    ]);
}

function barisUji(int $jumlah): array
{
    $baris = [];

    for ($i = 1; $i <= $jumlah; $i++) {
        $baris[] = fxAdopsiBaris($i, '51020'.$i, 'Desa '.$i, $i === 1 ? 3 : 1, 4234);
    }

    return $baris;
}

it('mengexpor seluruh hasil saringan bukan hanya halaman aktif', function () {
    fakeEkspor(barisUji(60));

    $response = $this->get(route('dataspasial.skema.adopsi.export', ['sektor' => 'SK0']));

    $response->assertOk();
    $response->assertHeader('Content-Type', 'application/vnd.ms-excel');
    expect($response->headers->get('Content-Disposition'))->toContain('adopsi-skema-SK0-');
    expect($response->headers->get('Content-Disposition'))->toContain('.xls');

    // 60 baris harus ada walau tabel hanya 25 baris per halaman.
    $isi = $response->getContent();
    expect(substr_count((string) $isi, '<tr>'))->toBeGreaterThan(60);
});

it('menyertakan keterangan waktu pembuatan dan kriteria saringan pada berkas', function () {
    fakeEkspor(barisUji(3));

    $isi = (string) $this->get(route('dataspasial.skema.adopsi.export', [
        'sektor' => 'SK0',
        'status' => 'tertinggal',
        'q' => 'Desa 2',
    ]))->getContent();

    expect($isi)->toContain('Waktu pembuatan');
    expect($isi)->toContain('Kriteria saringan');
    expect($isi)->toContain('tertinggal');
    expect($isi)->toContain('Desa 2');
});

it('hanya mengekspor desa yang ditandai tertinggal saat disaring status tertinggal', function () {
    fakeEkspor(barisUji(5));

    $isi = (string) $this->get(route('dataspasial.skema.adopsi.export', [
        'sektor' => 'SK0',
        'status' => 'tertinggal',
    ]))->getContent();

    // Desa 1 memakai versi terbaru (Terpusat) sehingga tidak ikut.
    expect($isi)->toContain('Desa 2');
    expect($isi)->not->toContain('>Desa 1<');
});

it('menampilkan pesan dan tidak mengunduh berkas kosong saat hasil saringan kosong', function () {
    fakeEkspor(barisUji(3));

    $this->getJson(route('dataspasial.skema.adopsi.export', ['sektor' => 'SK0', 'q' => 'tidak-ada']))
        ->assertStatus(422)
        ->assertJsonPath('success', false);
});

it('tidak pernah mengirim permintaan terbitan saat mengekspor', function () {
    fakeEkspor(barisUji(3));

    $this->get(route('dataspasial.skema.adopsi.export', ['sektor' => 'SK0']))->assertOk();

    Http::assertNotSent(fn (Request $r): bool => str_ends_with($r->url(), '/terbit')
        || str_contains($r->url(), '/terbit/preview'));
});

it('menampilkan data sementara tidak tersedia saat sumber adopsi gagal', function () {
    Http::fake([
        '*/api/v1/dataspasial/adopsi*' => Http::response(['message' => 'boom'], 500),
        '*/api/v1/dataspasial/sektor*' => Http::response(fakeKoleksi([fxSektor('4234', 'SK0', 'Sektor 0')]), 200),
        '*/api/v1/config/*' => Http::response(fakeConfigEndpoint(), 200),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->getJson(route('dataspasial.skema.adopsi.export', ['sektor' => 'SK0']))
        ->assertStatus(503)
        ->assertJsonPath('message', 'Data sementara tidak tersedia.');
});
