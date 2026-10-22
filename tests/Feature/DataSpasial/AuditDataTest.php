<?php

declare(strict_types=1);

namespace Tests\Feature\DataSpasial;

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(DatabaseTransactions::class);

/**
 * KAB-08 / API-08 — Halaman Log Entri Audit DataSpasial.
 *
 * Kontrak fixture di bawah adalah SALINAN dari bentuk respons API Satu Data
 * (`GET /api/v1/dataspasial/audit`, `/audit/ringkasan`, `/audit/{id}` —
 * repo `API-Database-Gabungan`, `AuditTransformer` + `AuditController`).
 */
class AuditDataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.databaseGabunganUrl' => 'https://api.example.test', 'dataspasial.cache_ttl' => 300]);
        Setting::query()->updateOrCreate(['key' => 'database_gabungan_api_key'], ['value' => 'test-token']);
        Cache::flush();
    }

    private function actingAsPembaca(): void
    {
        $this->actingAs(makeRole('pembaca audit uji k08', ['dataspasial-audit-read']));
    }

    // -----------------------------------------------------------------------
    // Akses
    // -----------------------------------------------------------------------

    public function test_guest_is_redirected(): void
    {
        $this->get('/dataspasial/audit')->assertRedirect('/login');
    }

    public function test_user_without_permission_is_rejected(): void
    {
        $tanpaIzin = makeRole('tanpa audit uji k08', []);

        $this->actingAs($tanpaIzin)
            ->get('/dataspasial/audit')
            ->assertForbidden();

        $this->actingAs($tanpaIzin)
            ->getJson('/dataspasial/audit/data')
            ->assertForbidden();
    }

    public function test_page_renders_with_filter_and_table(): void
    {
        $this->actingAsPembaca();
        $this->fakeAudit();

        $this->get('/dataspasial/audit')
            ->assertOk()
            ->assertSee('data-testid="audit-filter"', false)
            ->assertSee('data-testid="tabel-audit"', false)
            ->assertSee('data-testid="filter-sumber"', false)
            ->assertSee('data-testid="filter-desa"', false)
            ->assertSee('data-testid="filter-aksi"', false)
            ->assertSee('data-testid="filter-pelaku"', false)
            ->assertSee('data-testid="filter-waktu-dari"', false);
    }

    // -----------------------------------------------------------------------
    // Tabel & badge sumber
    // -----------------------------------------------------------------------

    public function test_table_shows_waktu_pelaku_desa_sektor_aksi_dan_sumber(): void
    {
        $this->actingAsPembaca();
        $this->fakeAudit();

        $resp = $this->getJson('/dataspasial/audit/data')->assertOk();
        $rows = $resp->json('tabel.rows');

        $this->assertCount(2, $rows);

        $openkab = $rows[0];
        $this->assertSame('openkab', $openkab['sumber']);
        $this->assertSame('OpenKab', $openkab['sumber_label']);
        $this->assertSame('badge-primary', $openkab['sumber_badge']);
        $this->assertSame('Budi Santoso', $openkab['pelaku_nama']);
        $this->assertSame('Infrastruktur', $openkab['sektor_nama']);
        $this->assertSame('update', $openkab['aksi']);
        $this->assertSame('Ubah', $openkab['aksi_label']);
        $this->assertSame('2025-06', $openkab['periode']);
        $this->assertNotEmpty($openkab['waktu']);

        $opensid = $rows[1];
        $this->assertSame('opensid', $opensid['sumber']);
        $this->assertSame('OpenSID', $opensid['sumber_label']);
        $this->assertSame('badge-info', $opensid['sumber_badge']);
    }

    public function test_badge_sources_use_different_colors(): void
    {
        $this->actingAsPembaca();
        $this->fakeAudit();

        $rows = $this->getJson('/dataspasial/audit/data')->assertOk()->json('tabel.rows');

        $this->assertNotSame($rows[0]['sumber_badge'], $rows[1]['sumber_badge']);
    }

    // -----------------------------------------------------------------------
    // Filter
    // -----------------------------------------------------------------------

    public function test_filter_sumber_is_forwarded_to_api(): void
    {
        $this->actingAsPembaca();
        $this->fakeAudit();

        $this->getJson('/dataspasial/audit/data?sumber=openkab')->assertOk();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'sumber=openkab');
        });
    }

    public function test_invalid_sumber_returns_422(): void
    {
        $this->actingAsPembaca();
        $this->fakeAudit();

        $resp = $this->getJson('/dataspasial/audit/data?sumber=ngawur')->assertStatus(422);
        $this->assertNotEmpty($resp->json('meta.validation'));
    }

    public function test_filter_tanggal_dan_wilayah_diteruskan(): void
    {
        $this->actingAsPembaca();
        $this->fakeAudit();

        $this->getJson('/dataspasial/audit/data?kode_kecamatan=510201&kode_desa=5102012001&waktu_dari=2025-06-01&waktu_sampai=2025-06-30&sektor=SK0&aksi=update&pelaku=Budi')
            ->assertOk();

        Http::assertSent(function ($request) {
            $url = $request->url();

            return str_contains($url, 'kode_kecamatan=510201')
                && str_contains($url, 'kode_desa=5102012001')
                && str_contains($url, 'waktu_dari=2025-06-01')
                && str_contains($url, 'waktu_sampai=2025-06-30')
                && str_contains($url, 'sektor=SK0')
                && str_contains($url, 'aksi=update')
                && str_contains($url, 'pelaku=Budi');
        });
    }

    public function test_waktu_sampai_sebelum_dari_ditolak(): void
    {
        $this->actingAsPembaca();
        $this->fakeAudit();

        $this->getJson('/dataspasial/audit/data?waktu_dari=2025-06-30&waktu_sampai=2025-06-01')
            ->assertStatus(422);
    }

    // -----------------------------------------------------------------------
    // Ringkasan per sumber
    // -----------------------------------------------------------------------

    public function test_kartu_ringkasan_menampilkan_jumlah_entri_per_sumber(): void
    {
        $this->actingAsPembaca();
        $this->fakeAudit();

        $kartu = $this->getJson('/dataspasial/audit/data')->assertOk()->json('kartu');

        $this->assertSame(1200, $kartu['openkab']);
        $this->assertSame(3400, $kartu['opensid']);
        $this->assertSame(4600, $kartu['total']);
    }

    // -----------------------------------------------------------------------
    // Detail baris (sebelum/sesudah)
    // -----------------------------------------------------------------------

    public function test_detail_baris_menampilkan_perbandingan_sebelum_sesudah(): void
    {
        $this->actingAsPembaca();
        $this->fakeAudit();

        $detail = $this->getJson('/dataspasial/audit/detail/10234')->assertOk()->json('detail');

        $this->assertSame('openkab', $detail['sumber']);
        $this->assertSame('Budi Santoso', $detail['pelaku_nama']);
        $this->assertNotEmpty($detail['perubahan']);

        $lebarJalan = collect($detail['perubahan'])->firstWhere('key', 'lebar_jalan');
        $this->assertNotNull($lebarJalan);
        $this->assertSame('4', $lebarJalan['sebelum']);
        $this->assertSame('4.5', $lebarJalan['sesudah']);
        $this->assertTrue($lebarJalan['berubah']);
    }

    public function test_detail_yang_tidak_ada_menampilkan_pesan_ramah(): void
    {
        $this->actingAsPembaca();
        $this->fakeAudit();

        $resp = $this->getJson('/dataspasial/audit/detail/999999')->assertOk();
        $this->assertTrue($resp->json('meta.unavailable'));
    }

    // -----------------------------------------------------------------------
    // Ekspor
    // -----------------------------------------------------------------------

    public function test_ekspor_menghasilkan_berkas_excel_dengan_isi_sama_dengan_layar(): void
    {
        $this->actingAsPembaca();
        $this->fakeAudit();

        $rows = $this->getJson('/dataspasial/audit/data')->assertOk()->json('tabel.rows');

        $resp = $this->get('/dataspasial/audit/ekspor?sumber=openkab');
        $resp->assertOk();
        $resp->assertHeader('content-disposition');

        $path = $resp->streamedContent();
        $this->assertNotEmpty($path);
    }

    public function test_ekspor_tanpa_hasil_menampilkan_pesan_dan_tidak_mengunduh_berkas(): void
    {
        $this->actingAsPembaca();
        $this->fakeAudit([], true);

        $this->getJson('/dataspasial/audit/ekspor')
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    // -----------------------------------------------------------------------
    // Keadaan gagal
    // -----------------------------------------------------------------------

    public function test_api_gagal_menampilkan_data_sementara_tidak_tersedia(): void
    {
        $this->actingAsPembaca();

        Http::fake([
            '*/api/v1/dataspasial/audit*' => Http::response(['message' => 'boom'], 500),
            '*' => Http::response(['data' => []], 200),
        ]);

        $resp = $this->getJson('/dataspasial/audit/data')->assertOk();
        $this->assertTrue($resp->json('meta.unavailable'));
    }

    // -----------------------------------------------------------------------
    // Fixture
    // -----------------------------------------------------------------------

    private function fakeAudit(?array $entri = null, bool $kosong = false): void
    {
        $entri ??= [
            [
                'id' => '10234',
                'type' => 'ds_audit',
                'attributes' => [
                    'waktu' => '2025-06-15T10:30:00Z',
                    'sumber' => 'openkab',
                    'pelaku' => ['id' => 7, 'nama' => 'Budi Santoso'],
                    'desa' => [
                        'config_id' => 12,
                        'kode_desa' => '5102012001',
                        'nama_desa' => 'Bajera',
                        'kode_kecamatan' => '510201',
                        'nama_kecamatan' => 'Telaga',
                    ],
                    'aksi' => 'update',
                    'sasaran' => [
                        'entitas' => 'nilai',
                        'entitas_id' => 998,
                        'sektor' => ['id' => 1, 'kode' => 'SK0', 'nama' => 'Infrastruktur'],
                        'fasilitas_id' => 250,
                        'isian_id' => 5,
                        'periode' => '2025-06',
                    ],
                    'ringkasan_sebelum' => ['lebar_jalan' => 4],
                    'ringkasan_sesudah' => ['lebar_jalan' => 4.5],
                ],
            ],
            [
                'id' => '10235',
                'type' => 'ds_audit',
                'attributes' => [
                    'waktu' => '2025-06-16T08:00:00Z',
                    'sumber' => 'opensid',
                    'pelaku' => ['id' => null, 'nama' => 'Operator Desa'],
                    'desa' => [
                        'config_id' => 13,
                        'kode_desa' => '5102012005',
                        'nama_desa' => 'Dauhwaru',
                        'kode_kecamatan' => '510201',
                        'nama_kecamatan' => 'Telaga',
                    ],
                    'aksi' => 'create',
                    'sasaran' => [
                        'entitas' => 'nilai',
                        'entitas_id' => 999,
                        'sektor' => ['id' => 1, 'kode' => 'SK0', 'nama' => 'Infrastruktur'],
                        'fasilitas_id' => 250,
                        'isian_id' => 6,
                        'periode' => '2025-06',
                    ],
                    'ringkasan_sebelum' => null,
                    'ringkasan_sesudah' => ['panjang_jalan' => 120],
                ],
            ],
        ];

        $daftar = $kosong ? [] : $entri;

        Http::fake([
            '*/api/v1/dataspasial/audit/ringkasan*' => Http::response([
                'data' => [
                    'type' => 'ds_audit_ringkasan',
                    'attributes' => [
                        'periode' => ['dari' => '2025-06-01', 'sampai' => '2025-06-30'],
                        'per_sumber' => [
                            ['sumber' => 'openkab', 'jumlah' => 1200],
                            ['sumber' => 'opensid', 'jumlah' => 3400],
                        ],
                        'per_desa' => [
                            ['config_id' => 12, 'kode_desa' => '5102012001', 'nama_desa' => 'Bajera', 'jumlah' => 210],
                        ],
                    ],
                ],
            ], 200),
            '*/api/v1/dataspasial/audit/10234*' => Http::response([
                'data' => [
                    'id' => '10234',
                    'type' => 'ds_audit',
                    'attributes' => [
                        'waktu' => '2025-06-15T10:30:00Z',
                        'sumber' => 'openkab',
                        'pelaku' => ['id' => 7, 'nama' => 'Budi Santoso'],
                        'desa' => [
                            'config_id' => 12,
                            'kode_desa' => '5102012001',
                            'nama_desa' => 'Bajera',
                            'kode_kecamatan' => '510201',
                            'nama_kecamatan' => 'Telaga',
                        ],
                        'aksi' => 'update',
                        'sasaran' => [
                            'entitas' => 'nilai',
                            'entitas_id' => 998,
                            'sektor' => ['id' => 1, 'kode' => 'SK0', 'nama' => 'Infrastruktur'],
                            'periode' => '2025-06',
                        ],
                        'sebelum' => ['lebar_jalan' => 4, 'status' => 'draft'],
                        'sesudah' => ['lebar_jalan' => 4.5, 'status' => 'terbit'],
                    ],
                ],
            ], 200),
            '*/api/v1/dataspasial/audit/999999*' => Http::response(['message' => 'Catatan audit tidak ditemukan'], 404),
            '*/api/v1/dataspasial/audit*' => Http::response([
                'data' => $daftar,
                'meta' => [
                    'pagination' => [
                        'total' => count($daftar),
                        'count' => count($daftar),
                        'per_page' => 25,
                        'current_page' => 1,
                        'total_pages' => 1,
                    ],
                ],
            ], 200),
            '*/api/v1/config/*' => Http::response(['data' => []], 200),
            '*/api/v1/dataspasial/sektor*' => Http::response(['data' => []], 200),
            '*' => Http::response(['data' => []], 200),
        ]);
    }
}
