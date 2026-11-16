<?php

declare(strict_types=1);

use App\Services\SkemaVersiService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(DatabaseTransactions::class);

/**
 * US1/US2/US3 — perhitungan versi skema (FR-010 … FR-058).
 *
 * Fixture yang dipakai adalah SALINAN PERSIS respons API yang hidup
 * (contracts §4/§5) — lihat `tests/Feature/DataSpasial/Fixtures.php`.
 */
beforeEach(function () {
    config([
        'app.databaseGabunganUrl' => 'https://api.example.test',
        'dataspasial.cache_ttl' => 300,
    ]);

    Cache::flush();

    test()->adminSkema = makeAdminSkema();
    test()->actingAs(test()->adminSkema, 'web');
});

/**
 * Pasang stub riwayat versi + satu versi skema (dengan & tanpa isian terarsip).
 *
 * @param  array<int, array<string, mixed>>  $riwayat
 * @param  array<string, mixed>  $skemaPerVersi
 */
function fakeVersi(array $riwayat, ?array $skemaPerVersi = null): void
{
    Http::fake([
        '*/api/v1/config/*' => Http::response(fakeConfigEndpoint(), 200),
        '*/api/v1/dataspasial/skema/riwayat*' => Http::response(fakeKoleksi($riwayat), 200),
        '*/api/v1/dataspasial/skema?*' => Http::response($skemaPerVersi ?? fxSkemaVersiDenganArsip(), 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(fakeKoleksi([fxSektor('4234', 'SK0', 'Sektor 0')]), 200),
        '*' => Http::response(['data' => []], 200),
    ]);
}

/** Bentuk isian yang dipakai skrip (`SkemaEditorService::petakanIsian()`). */
function isianUji(array $ubah = []): array
{
    return array_merge([
        'kode' => 'lebar_jalan',
        'label' => 'Lebar Jalan',
        'tipe' => 'angka',
        'wajib' => true,
        'satuan' => 'meter',
        'opsi' => [],
        'urutan' => 1,
        'diarsipkan' => false,
        'popupPeta' => false,
        'sebagaiFilter' => false,
        'sebagaiIndikator' => false,
        'sumber' => 'manual',
        'rujukan' => null,
    ], $ubah);
}

// -----------------------------------------------------------------------
// FR-010 … FR-016 — ringkasan perubahan dari selisih nyata
// -----------------------------------------------------------------------

it('mengklasifikasikan isian baru diubah dan diarsipkan dari selisih nyata', function () {
    fakeVersi(fxRiwayatDuaVersi());

    $ringkasan = app(SkemaVersiService::class)->ringkasanPerubahan('SK0', [
        // `lebar_jalan` versi terbit TIDAK ada lagi pada draf -> diarsipkan.
        // `jumlah_siswa` tidak ada pada versi terbit -> baru.
        isianUji(['kode' => 'jumlah_siswa', 'label' => 'Jumlah Siswa', 'urutan' => 2]),
    ]);

    expect($ringkasan['ada_perubahan'])->toBeTrue();
    expect($ringkasan['terbitan_pertama'])->toBeFalse();
    expect($ringkasan['versi_terbit_sebelumnya'])->toBe(3);
    expect($ringkasan['jumlah_baru'])->toBe(1);
    expect($ringkasan['isian_baru'][0]['kode'])->toBe('jumlah_siswa');

    // `lebar_jalan` ada pada v3 (aktif) tetapi tidak ada pada draf -> diarsipkan.
    expect($ringkasan['jumlah_diarsipkan'])->toBe(1);
    expect($ringkasan['isian_diarsipkan'][0]['kode'])->toBe('lebar_jalan');
});

it('menampilkan rincian nilai lama ke baru untuk isian yang diubah', function () {
    fakeVersi(fxRiwayatDuaVersi());

    $ringkasan = app(SkemaVersiService::class)->ringkasanPerubahan('SK0', [
        isianUji(['wajib' => false]), // wajib: yes -> no
    ]);

    expect($ringkasan['jumlah_diubah'])->toBe(1);
    expect($ringkasan['isian_diubah'][0]['kode'])->toBe('lebar_jalan');
    expect($ringkasan['isian_diubah'][0]['perubahan'])->toContain([
        'atribut' => 'wajib', 'dari' => 'ya', 'ke' => 'tidak',
    ]);
});

it('menyatakan seluruh isian sebagai baru pada terbitan pertama', function () {
    fakeVersi([fxRiwayatVersi('900', 4234, 1, 'draf')]);

    $ringkasan = app(SkemaVersiService::class)->ringkasanPerubahan('SK0', [
        isianUji(['kode' => 'a']),
    ]);

    expect($ringkasan['terbitan_pertama'])->toBeTrue();
    expect($ringkasan['versi_terbit_sebelumnya'])->toBeNull();
    expect($ringkasan['jumlah_baru'])->toBe(1);
    expect($ringkasan['jumlah_diarsipkan'])->toBe(0);
});

it('tidak menemukan perubahan ketika draf identik dengan versi terbit', function () {
    fakeVersi([fxRiwayatVersi('8143', 4234, 3, 'terbit')], fxSkemaVersiDenganArsip([
        fxIsian('lebar_jalan', [
            'label' => 'Lebar Jalan',
            'tipe' => 'angka',
            'wajib' => true,
            'satuan' => 'meter',
            'urutan' => 1,
            'tampil_popup' => true,
        ]),
    ]));

    $ringkasan = app(SkemaVersiService::class)->ringkasanPerubahan('SK0', [
        isianUji(['popupPeta' => true]),
    ]);

    expect($ringkasan['ada_perubahan'])->toBeFalse();
    expect($ringkasan['total_perubahan'])->toBe(0);
});

it('membatasi daftar nama isian pada sepuluh lalu menambahkan penanda sisanya', function () {
    fakeVersi([fxRiwayatVersi('900', 4234, 1, 'draf')]);

    $draf = [];

    for ($i = 1; $i <= 13; $i++) {
        $draf[] = isianUji(['kode' => 'isian_'.$i, 'label' => 'Isian '.$i, 'urutan' => $i]);
    }

    $ringkasan = app(SkemaVersiService::class)->ringkasanPerubahan('SK0', $draf);

    expect($ringkasan['jumlah_baru'])->toBe(13);
    expect(count($ringkasan['isian_baru']))->toBe(SkemaVersiService::BATAS_NAMA_ISIAN + 1);
    expect(end($ringkasan['isian_baru'])['sisa'])->toBe(3);
});

// -----------------------------------------------------------------------
// FR-045 — pra-pemeriksaan yang pasti ditolak server
// -----------------------------------------------------------------------

it('menolak draf tanpa isian aktif sebelum dikirim', function () {
    fakeVersi(fxRiwayatDuaVersi());

    $hasil = app(SkemaVersiService::class)->praPemeriksaanTerbit('SK0', [
        isianUji(['diarsipkan' => true]),
    ]);

    expect($hasil['polos'])->toBeFalse();
    expect($hasil['alasan'])->toContain('isian aktif');
});

it('menolak perubahan tipe isian yang sudah terbit sebelum dikirim', function () {
    fakeVersi(fxRiwayatDuaVersi());

    $hasil = app(SkemaVersiService::class)->praPemeriksaanTerbit('SK0', [
        isianUji(['tipe' => 'teks', 'popupPeta' => true]),
    ]);

    expect($hasil['polos'])->toBeFalse();
    expect($hasil['alasan'])->toContain('Tipe data');
    expect($hasil['isian'])->toContain('lebar_jalan');
});

it('meloloskan draf yang tidak mengubah tipe isian terbit', function () {
    fakeVersi(fxRiwayatDuaVersi());

    $hasil = app(SkemaVersiService::class)->praPemeriksaanTerbit('SK0', [
        isianUji(['label' => 'Lebar Jalan (meter)', 'popupPeta' => true]),
    ]);

    expect($hasil['polos'])->toBeTrue();
});

// -----------------------------------------------------------------------
// FR-050 / FR-052 — riwayat & perbandingan
// -----------------------------------------------------------------------

it('menampilkan tiga status versi termasuk usang dan catatan kosong', function () {
    fakeVersi(fxRiwayatDenganUsang());

    $riwayat = app(SkemaVersiService::class)->riwayatSektor('SK0');

    expect($riwayat['tersedia'])->toBeTrue();
    expect($riwayat['versi'])->toHaveCount(2);
    expect($riwayat['versi'][0]['status_label'])->toBe('Terbit');
    expect($riwayat['versi'][1]['status_label'])->toBe('Usang');
    // Versi tanpa catatan tetap tampil dengan keterangan (FR-054).
    expect($riwayat['versi'][1]['catatan_label'])->toBe('(tanpa catatan)');
});

it('menandai status perbandingan tetap baru diubah dan diarsipkan', function () {
    $versiLama = [
        isianUji(['kode' => 'tetap']),
        isianUji(['kode' => 'berubah', 'label' => 'Berubah']),
        isianUji(['kode' => 'lama', 'label' => 'Lama']),
    ];

    $versiBaru = [
        isianUji(['kode' => 'tetap']),
        isianUji(['kode' => 'berubah', 'label' => 'Berubah', 'wajib' => false]),
        isianUji(['kode' => 'baru', 'label' => 'Baru']),
    ];

    $baris = app(SkemaVersiService::class)->bandingkanDuaVersi($versiLama, $versiBaru);
    $peta = [];

    foreach ($baris as $b) {
        $peta[$b['kode']] = $b;
    }

    expect($peta['tetap']['status'])->toBe('tetap');
    expect($peta['berubah']['status'])->toBe('diubah');
    expect($peta['baru']['status'])->toBe('baru');
    // FR-055 — isian hilang/terarsip tetap terlihat di perbandingan.
    expect($peta['lama']['status'])->toBeIn(['hilang', 'diarsipkan']);
});

it('mengambil isian satu versi lewat parameter versi beserta isian terarsip', function () {
    fakeVersi(fxRiwayatDuaVersi());

    $hasil = app(SkemaVersiService::class)->isianVersi('SK0', 3);

    expect($hasil['tersedia'])->toBeTrue();
    expect($hasil['versi']['nomorVersi'])->toBe(3);
    expect(count($hasil['isian']))->toBe(2);

    Http::assertSent(function (Request $request): bool {
        return str_contains($request->url(), 'versi=3')
            && str_contains($request->url(), 'termasuk_arsip=1');
    });
});
