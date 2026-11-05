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
 * US1 — Status pengisian seluruh desa + kartu (KAB-07).
 *
 * Kontrak diverifikasi langsung (Fixtures.php blok KAB-07 + contracts §8):
 * `sumber_data` pada nilai, ringkasan per-wilayah tanpa meta, cakupan desa
 * dari adopsi (+ get-list-desa untuk satu kecamatan).
 */
class MonitoringDataTest extends TestCase
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
        $this->actingAs(makeRole('pembaca monitoring uji k07', ['dataspasial-monitoring-read']));
    }

    private function fakeWilayah(): void
    {
        Http::fake([
            // ConfigApiService::desa() → GET /api/v1/config/desa?filter[kode_kecamatan]=…
            '*/api/v1/config/desa*' => Http::response([
                'data' => [
                    ['type' => 'config', 'id' => '1', 'attributes' => ['kode_desa' => '5102012001', 'nama_desa' => 'Bajera', 'kode_kecamatan' => '510201']],
                    ['type' => 'config', 'id' => '5', 'attributes' => ['kode_desa' => '5102012005', 'nama_desa' => 'Dauhwaru', 'kode_kecamatan' => '510201']],
                    ['type' => 'config', 'id' => '6', 'attributes' => ['kode_desa' => '5102012006', 'nama_desa' => 'Epal', 'kode_kecamatan' => '510201']],
                    ['type' => 'config', 'id' => '99', 'attributes' => ['kode_desa' => '5102012099', 'nama_desa' => 'Belum Terdaftar', 'kode_kecamatan' => '510201']],
                ],
            ], 200),
        ]);
    }

    public function test_guest_is_redirected(): void
    {
        $this->get('/dataspasial/monitoring')->assertRedirect('/login');
    }

    public function test_user_without_permission_is_rejected(): void
    {
        $tanpaIzin = makeRole('tanpa monitoring uji k07', []);
        $this->actingAs($tanpaIzin)
            ->get('/dataspasial/monitoring/data?sektor=SK0&kode_kecamatan=SEMUA')
            ->assertForbidden();
    }

    public function test_page_renders_without_data_desa(): void
    {
        $this->actingAsPembaca();
        fakeEndpointMonitoring();
        $this->get('/dataspasial/monitoring')
            ->assertOk()
            ->assertSee('data-testid="monitoring-filter"', false)
            ->assertSee('data-testid="kartu-sudah"', false)
            ->assertSee('data-testid="kartu-belum"', false)
            ->assertSee('data-testid="kartu-periksa"', false)
            ->assertSee('data-testid="tabel-monitoring"', false);
    }

    public function test_data_requires_sektor_but_kecamatan_is_optional(): void
    {
        $this->actingAsPembaca();
        fakeEndpointMonitoring();

        $tanpaSektor = $this->getJson('/dataspasial/monitoring/data')
            ->assertStatus(422);
        $this->assertNotEmpty($tanpaSektor->json('meta.validation'));

        // Kecamatan opsional (keputusan 2026-10-21): tanpa kecamatan = Semua.
        $tanpaKecamatan = $this->getJson('/dataspasial/monitoring/data?sektor=SK0&periode=2025-06')
            ->assertOk();
        $this->assertSame('SEMUA', $tanpaKecamatan->json('meta.kode_kecamatan'));
        $this->assertCount(5, $tanpaKecamatan->json('tabel.rows'));
    }

    public function test_all_desa_appear_including_never_submitted(): void
    {
        $this->actingAsPembaca();
        fakeEndpointMonitoring();

        $resp = $this->getJson('/dataspasial/monitoring/data?sektor=SK0&kode_kecamatan=SEMUA&periode=2025-06')
            ->assertOk();

        $kode = collect($resp->json('tabel.rows'))->pluck('kode_desa')->all();
        // SC-001: seluruh desa adopsi tampil — termasuk belum_ada_data (Cekatak).
        $this->assertSame(
            ['5102022003', '5102022004', '5102012001', '5102012005', '5102012006'],
            $kode,
            'Seluruh desa adopsi tampil, urutan: perlu tindakan lalu nama'
        );
    }

    public function test_cards_and_row_semantics_match_fixtures(): void
    {
        $this->actingAsPembaca();
        fakeEndpointMonitoring();

        $resp = $this->getJson('/dataspasial/monitoring/data?sektor=SK0&kode_kecamatan=SEMUA&periode=2025-06')
            ->assertOk();

        // Kartu dasar (aturan tanda dihapus): sudah=4, belum=1, perlu_diperiksa = tertinggal + belum (perlu tindakan).
        $this->assertSame(
            ['sudah_mengisi' => 4, 'belum_pernah_mengirim' => 1, 'perlu_diperiksa' => 2, 'total_desa' => 5, 'sudah_mengisi_label' => '4/5'],
            $resp->json('kartu')
        );

        $rows = collect($resp->json('tabel.rows'))->keyBy('kode_desa');

        // D1 Bajera — sudah mengisi, mutakhir (v3=terbit), sumber manual, kelengkapan 3/4=75.
        $bajera = $rows['5102012001'];
        $this->assertSame('sudah_mengisi', $bajera['status_pengisian']);
        $this->assertSame('mutakhir', $bajera['status_versi']);
        $this->assertSame('manual', $bajera['sumber']);
        $this->assertFalse($bajera['tanda_impor']);
        $this->assertSame(75, $bajera['kelengkapan_persen']);

        // Kolom Kecamatan: kode dari prefiks kode_desa, nama dari config.
        $this->assertSame('510201', $bajera['kode_kecamatan']);
        $this->assertSame('Kecamatan Kediri', $bajera['nama_kecamatan']);
        $this->assertSame(
            ['desa', 'kecamatan', 'kelengkapan', 'kualitas', 'status'],
            $resp->json('tabel.columns')
        );

        // D2 Bantas — tertinggal (v2 < v3).
        $this->assertSame('tertinggal', $rows['5102022003']['status_versi']);

        // D3 Cekatak — belum pernah mengirim, versi "—".
        $cekatak = $rows['5102022004'];
        $this->assertSame('belum_pernah_mengirim', $cekatak['status_pengisian']);
        $this->assertNull($cekatak['versi_terpakai']);
        $this->assertSame(0, $cekatak['kelengkapan_persen']);
        $this->assertSame(null, $cekatak['sumber']);

        // D4 Dauhwaru — anomali versi (v4 > terbit v3), sumber pemetaan tanpa tanda impor.
        $dauhwaru = $rows['5102012005'];
        $this->assertSame('anomali', $dauhwaru['status_versi']);
        $this->assertSame('pemetaan', $dauhwaru['sumber']);
        $this->assertFalse($dauhwaru['tanda_impor']);

        // D5 Epal — sumber impor ditandai (Q3:A).
        $epal = $rows['5102012006'];
        $this->assertSame('impor', $epal['sumber']);
        $this->assertTrue($epal['tanda_impor']);
    }

    public function test_pagination_works_without_status_or_name_filters(): void
    {
        $this->actingAsPembaca();
        fakeEndpointMonitoring();

        // Filter status & cari nama desa DIHAPUS (keputusan 2026-10-21) —
        // parameter lama diabaikan tanpa error.
        $abaikan = $this->getJson('/dataspasial/monitoring/data?sektor=SK0&kode_kecamatan=SEMUA&periode=2025-06&status=tertinggal&q=baj')
            ->assertOk();
        $this->assertCount(5, $abaikan->json('tabel.rows'), 'status & q diabaikan');

        $hal2 = $this->getJson('/dataspasial/monitoring/data?sektor=SK0&kode_kecamatan=SEMUA&periode=2025-06&page=2&per_page=2')
            ->assertOk();
        $this->assertSame(2, $hal2->json('meta.page'));
        $this->assertSame(5, $hal2->json('meta.total'));
        $this->assertSame(3, $hal2->json('meta.total_pages'));
        $this->assertCount(2, $hal2->json('tabel.rows'));
    }

    public function test_kecamatan_scope_uses_wilayah_list_and_keeps_unregistered_desa(): void
    {
        $this->actingAsPembaca();
        // Urutan penting: stub spesifik config/desa didaftarkan sebelum
        // catch-all pada fakeEndpointMonitoring (stub pertama yang cocok menang).
        $this->fakeWilayah();
        fakeEndpointMonitoring();

        $resp = $this->getJson('/dataspasial/monitoring/data?sektor=SK0&kode_kecamatan=510201&periode=2025-06')
            ->assertOk();

        $kode = collect($resp->json('tabel.rows'))->pluck('kode_desa')->all();
        // Hanya desa kecamatan 510201; desa wilayah yang belum ada di adopsi tetap tampil.
        $this->assertSame(
            ['5102012099', '5102012001', '5102012005', '5102012006'],
            $kode
        );
        $belumTerdaftar = collect($resp->json('tabel.rows'))->firstWhere('kode_desa', '5102012099');
        $this->assertSame('belum_pernah_mengirim', $belumTerdaftar['status_pengisian']);
    }

    public function test_periods_default_from_adopsi_and_unknown_period_is_empty(): void
    {
        $this->actingAsPembaca();
        fakeEndpointMonitoring();

        $tanpaPeriode = $this->getJson('/dataspasial/monitoring/data?sektor=SK0&kode_kecamatan=SEMUA')
            ->assertOk();
        $this->assertSame('2025-06', $tanpaPeriode->json('meta.periode'), 'Default = periode terbaru berdata');
        $this->assertSame(['2025-06', '2025-05'], $tanpaPeriode->json('meta.periods'));

        $kosong = $this->getJson('/dataspasial/monitoring/data?sektor=SK0&kode_kecamatan=SEMUA&periode=2024-01')
            ->assertOk();
        $this->assertTrue($kosong->json('meta.empty'));
        $this->assertSame([], $kosong->json('tabel.rows'));
    }

    public function test_unavailable_returns_generic_message(): void
    {
        $this->actingAsPembaca();
        Http::fake([
            '*/api/v1/dataspasial/adopsi*' => Http::response(['message' => 'boom'], 500),
            '*' => Http::response(['data' => []], 200),
        ]);

        $resp = $this->getJson('/dataspasial/monitoring/data?sektor=SK0&kode_kecamatan=SEMUA')
            ->assertOk();
        $this->assertTrue($resp->json('meta.unavailable'));
        $this->assertSame([], $resp->json('tabel.rows'));
    }

    public function test_row_order_puts_action_needed_first(): void
    {
        $this->actingAsPembaca();
        fakeEndpointMonitoring();

        $kode = collect($this->getJson('/dataspasial/monitoring/data?sektor=SK0&kode_kecamatan=SEMUA&periode=2025-06')
            ->json('tabel.rows'))->pluck('kode_desa')->all();

        // Perlu tindakan: tertinggal (Bantas) & belum (Cekatak) lebih dulu — lalu nama menaik.
        $this->assertSame(['5102022003', '5102022004', '5102012001', '5102012005', '5102012006'], $kode);
    }
}
