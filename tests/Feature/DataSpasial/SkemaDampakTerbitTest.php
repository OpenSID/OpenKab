<?php

declare(strict_types=1);

use App\Enums\DataSpasialReason;
use App\Models\Setting;
use App\Services\DataSpasialResult;
use App\Services\SkemaVersiService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

uses(DatabaseTransactions::class);

/**
 * US3 — Peringatan dampak pengarsipan & bentrok lokal desa (FR-030 … FR-044).
 *
 * ⚠️ Endpoint pratinjau pra-terbit **belum ada** di API (Utang U10): sistem
 * menampilkan "belum dapat dihitung" dan **tidak pernah** mengarang angka
 * (FR-043/FR-071).
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

/** Draf berisi satu isian aktif dan satu isian terarsip. */
function drafDenganArsip(): array
{
    return [
        [
            'kode' => 'lebar_jalan',
            'label' => 'Lebar Jalan',
            'tipe' => 'angka',
            'wajib' => true,
            'satuan' => 'meter',
            'urutan' => 1,
            'diarsipkan' => false,
        ],
        [
            'kode' => 'arsip_lama',
            'label' => 'Arsip Lama',
            'tipe' => 'teks',
            'wajib' => false,
            'urutan' => 2,
            'diarsipkan' => true,
        ],
    ];
}

// -----------------------------------------------------------------------
// FR-040 … FR-044 — perilaku jujur saat endpoint belum ada
// -----------------------------------------------------------------------

it('menampilkan daftar isian diarsipkan dan menyebut jumlah belum dapat dihitung', function () {
    $peringatan = app(SkemaVersiService::class)->peringatanTerbit(
        drafDenganArsip(),
        DataSpasialResult::unavailable(DataSpasialReason::fromValue(DataSpasialReason::ClientError), 404)
    );

    expect($peringatan['jumlah_isian_diarsipkan'])->toBe(1);
    expect($peringatan['isian_diarsipkan'][0]['kode'])->toBe('arsip_lama');
    expect($peringatan['dampak_tersedia'])->toBeFalse();
    expect($peringatan['pesan_dampak'])->toBe('Jumlah data terdampak belum dapat dihitung.');

    // FR-043/FR-071: tidak boleh 0, tidak boleh perkiraan.
    expect($peringatan['jumlah_nilai_terdampak'])->toBeNull();
    expect($peringatan['jumlah_desa_terdampak'])->toBeNull();
    expect($peringatan['jumlah_fasilitas_terdampak'])->toBeNull();
});

it('menyatakan bahwa nilai lama tidak dihapus', function () {
    $peringatan = app(SkemaVersiService::class)->peringatanTerbit(drafDenganArsip());

    expect($peringatan['penjelasan_arsip'])->toContain('tidak dihapus');
});

it('meminta pengakuan eksplisit ketika pemeriksaan bentrok belum dapat dilakukan', function () {
    $peringatan = app(SkemaVersiService::class)->peringatanTerbit(drafDenganArsip());

    expect($peringatan['bentrok_tersedia'])->toBeFalse();
    expect($peringatan['pesan_bentrok'])->toBe('Pemeriksaan isian lokal desa belum dapat dilakukan.');
    expect($peringatan['butuh_pengakuan'])->toBeTrue();
});

it('menampilkan daftar desa bentrok ketika endpoint pratinjau tersedia', function () {
    $pratinjau = DataSpasialResult::data([[
        'type' => 'pra-terbit',
        'id' => '1',
        'attributes' => [
            'sektor_id' => 4234,
            'isian_diarsipkan' => [
                ['kode' => 'arsip_lama', 'label' => 'Arsip Lama', 'jumlah_nilai' => 12, 'jumlah_desa' => 3, 'jumlah_fasilitas' => 5],
            ],
            'desa_bentrok' => [
                ['config_id' => 1, 'kode_desa' => '5102012001', 'nama_desa' => 'Bajera', 'isian' => [
                    ['kode' => 'arsip_lama', 'alasan' => 'isian diarsipkan sementara masih terisi'],
                ]],
            ],
        ],
    ]]);

    $peringatan = app(SkemaVersiService::class)->peringatanTerbit(drafDenganArsip(), $pratinjau);

    expect($peringatan['dampak_tersedia'])->toBeTrue();
    expect($peringatan['jumlah_nilai_terdampak'])->toBe(12);
    expect($peringatan['jumlah_desa_terdampak'])->toBe(3);
    expect($peringatan['jumlah_fasilitas_terdampak'])->toBe(5);
    expect($peringatan['bentrok_tersedia'])->toBeTrue();
    expect($peringatan['jumlah_desa_bentrok'])->toBe(1);
    expect($peringatan['desa_bentrok'][0]['nama_desa'])->toBe('Bajera');
    expect($peringatan['butuh_pengakuan'])->toBeTrue();
    expect($peringatan['pesan_dampak'])->toBeNull();
});

it('menampilkan peringatan lengkap melalui endpoint preview', function () {
    Http::fake([
        '*/api/v1/config/*' => Http::response(fakeConfigEndpoint(), 200),
        '*/api/v1/dataspasial/sektor*' => Http::response(fakeKoleksi([fxSektor('4234', 'SK0', 'Sektor 0')]), 200),
        '*/api/v1/dataspasial/skema/riwayat*' => Http::response(fakeKoleksi(fxRiwayatDuaVersi()), 200),
        '*/api/v1/dataspasial/skema/*/pra-terbit*' => Http::response(['message' => 'Not found'], 404),
        '*/api/v1/dataspasial/skema?*' => Http::response(fxSkemaVersiDenganArsip(), 200),
        '*' => Http::response(['data' => []], 200),
    ]);

    $response = $this->postJson(route('dataspasial.skema.terbit.preview'), [
        'sektor' => 'SK0',
        'isian' => drafDenganArsip(),
    ]);

    $response->assertOk();
    $response->assertJsonPath('peringatan.dampak_tersedia', false);
    $response->assertJsonPath('peringatan.jumlah_isian_diarsipkan', 1);
    $response->assertJsonPath('peringatan.butuh_pengakuan', true);
    $response->assertJsonPath('peringatan.pesan_dampak', 'Jumlah data terdampak belum dapat dihitung.');
});

it('mencatat jejak audit saat penerbitan memakai pengakuan bentrok', function () {
    Log::spy();

    Http::fake([
        '*/api/v1/dataspasial/skema/*/terbit' => Http::response([
            'data' => [
                'type' => 'skema data spasial',
                'id' => '8143',
                'attributes' => [
                    'versi_skema' => ['id' => 8143, 'nomor_versi' => 3, 'status' => 'terbit'],
                    'isian' => [],
                ],
            ],
        ], 200),
        '*' => Http::response(['data' => []], 200),
    ]);

    $this->postJson(route('dataspasial.skema.terbit'), [
        'sektor' => 'SK0',
        'catatan_perubahan' => 'Menerbitkan dengan pengakuan bentrok.',
        'pengakuan_bentrok' => true,
    ])->assertOk();

    Log::shouldHaveReceived('info')
        ->withArgs(fn (string $pesan, array $ctx = []): bool => str_contains($pesan, 'diterbitkan')
            && ($ctx['pengakuan_bentrok'] ?? false) === true
            && ($ctx['sektor'] ?? null) === 'SK0')
        ->once();
});
