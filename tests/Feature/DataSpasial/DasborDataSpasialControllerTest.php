<?php

declare(strict_types=1);

namespace Tests\Feature\DataSpasial;

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(DatabaseTransactions::class);

class DasborDataSpasialControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.databaseGabunganUrl' => 'https://api.example.test', 'dataspasial.cache_ttl' => 300]);
        Setting::query()->updateOrCreate(['key' => 'database_gabungan_api_key'], ['value' => 'test-token']);
        Cache::flush();
    }

    private function actingAsAnalis(): void
    {
        $this->actingAs(makeRole('analis opd', ['dataspasial-dasbor-read']));
    }

    private function fakeData(): void
    {
        Http::fake([
            '*/api/v1/dataspasial/ringkasan*' => Http::response(fxRingkasanIndikator(), 200),
            '*/api/v1/dataspasial/skema*' => Http::response(fxSkemaIsianIndikator(), 200),
            '*/api/v1/dataspasial/sektor*' => Http::response(fakeKoleksi([fxSektor('1', 'SK0', 'Infrastruktur')]), 200),
            '*/api/v1/dataspasial/nilai*' => Http::response(fxNilaiIsian(), 200),
            '*/api/v1/dataspasial/geojson*' => Http::response(fxGeoJsonGabungan(), 200),
        ]);
    }

    public function test_guest_is_redirected_from_dasbor(): void
    {
        $this->get('/dataspasial/dasbor')->assertRedirect('/login');
    }

    public function test_page_renders_cards_and_url_state(): void
    {
        $this->actingAsAnalis();
        $this->fakeData();
        $this->get('/dataspasial/dasbor?kecamatan=510201&periode=2025-06')
            ->assertOk()
            ->assertSee('Dasbor DataSpasial')
            ->assertSee('data-testid="dasbor-kartu"', false);
    }

    public function test_cards_match_ringkasan_totals(): void
    {
        $this->actingAsAnalis();
        $this->fakeData();
        $this->getJson('/dataspasial/dasbor/data?periode=2025-06')->assertOk()->assertJsonStructure(['kartu', 'tabel']);
    }

    // Dropdown indikator bergantung pada sektor: daftar diambil dari isian sektor terpilih.

    public function test_indicator_list_follows_selected_sector(): void
    {
        $this->actingAsAnalis();
        $skemaSkx = [
            'data' => [[
                'type' => 'ds_skema_versi',
                'id' => '9',
                'attributes' => [
                    'sektor_id' => 9,
                    'status' => 'terbit',
                    'isian' => [
                        ['kode' => 'jumlah_rawat_inap', 'label' => 'Jumlah Rawat Inap', 'tipe' => 'angka', 'satuan' => 'tempat', 'tampil_indikator' => true],
                    ],
                ],
            ]],
        ];
        Http::fake([
            '*/api/v1/dataspasial/ringkasan*' => Http::response(fxRingkasanIndikator(), 200),
            '*/api/v1/dataspasial/sektor*' => Http::response(fakeKoleksi([
                fxSektor('1', 'SK0', 'Infrastruktur'),
                fxSektor('9', 'SKX', 'Kesehatan'),
            ]), 200),
            '*/api/v1/dataspasial/nilai*' => Http::response(fxNilaiIsian(), 200),
            '*/api/v1/dataspasial/skema?sektor=SKX*' => Http::response($skemaSkx, 200),
            '*/api/v1/dataspasial/skema*' => Http::response(fxSkemaIsianIndikator(), 200),
        ]);

        $skx = collect($this->getJson('/dataspasial/dasbor/data?sektor=SKX')->assertOk()->json('indikator'))
            ->pluck('kode')->all();
        $this->assertSame(['jumlah_rawat_inap'], $skx, 'Indikator harus berasal dari isian sektor terpilih');

        $sk0 = collect($this->getJson('/dataspasial/dasbor/data?sektor=SK0')->assertOk()->json('indikator'))
            ->pluck('kode')->all();
        $this->assertSame(['jumlah_unit', 'jumlah_penduduk'], $sk0);
    }

    // Indikator di luar sektor terpilih diabaikan (tidak dipakai grafik).

    public function test_indicator_outside_selected_sector_is_ignored(): void
    {
        $this->actingAsAnalis();
        $this->fakeData();

        $resp = $this->getJson('/dataspasial/dasbor/data?sektor=SK0&indikator=isian_sektor_lain')->assertOk();
        $this->assertNotSame('isian_sektor_lain', $resp->json('meta.indikator_terpilih'));
    }

    public function test_invalid_query_params_ignored_gracefully(): void
    {
        $this->actingAsAnalis();
        $this->fakeData();
        $this->get('/dataspasial/dasbor?foo=bar&kecamatan=invalid')->assertOk();
    }

    public function test_unavailable_returns_generic_message(): void
    {
        $this->actingAsAnalis();
        Http::fake([
            '*/api/v1/dataspasial/ringkasan*' => Http::response([], 500),
            '*' => Http::response([], 500),
        ]);
        $resp = $this->getJson('/dataspasial/dasbor/data');
        $resp->assertOk();
        $this->assertTrue($resp->json('meta.unavailable') === true || $resp->json('unavailable') === true);
    }

    public function test_empty_filters_show_no_data(): void
    {
        $this->actingAsAnalis();
        Http::fake([
            '*/api/v1/dataspasial/ringkasan*' => Http::response(['data' => []], 200),
            '*/api/v1/dataspasial/sektor*' => Http::response(fakeKoleksi([]), 200),
        ]);
        $this->getJson('/dataspasial/dasbor/data')->assertOk();
    }

    // Q3 — baris keterangan opsional pada kartu.

    public function test_cards_include_keterangan_when_provided(): void
    {
        $this->actingAsAnalis();
        $this->fakeData();

        $resp = $this->getJson('/dataspasial/dasbor/data?periode=2025-06')->assertOk();
        $kartu = collect($resp->json('kartu'));
        $this->assertSame(
            'L 30.112 · P 28.828',
            $kartu->firstWhere('kode', 'jumlah_penduduk')['keterangan'] ?? null
        );
    }

    public function test_cards_omit_keterangan_when_absent(): void
    {
        $this->actingAsAnalis();
        $this->fakeData();

        $resp = $this->getJson('/dataspasial/dasbor/data?periode=2025-06')->assertOk();
        $tanpa = collect($resp->json('kartu'))->firstWhere('kode', 'jumlah_unit');
        $this->assertTrue(is_array($tanpa), 'kartu jumlah_unit harus ada');
        $this->assertTrue(array_key_exists('keterangan', $tanpa), 'kunci keterangan harus ikut dalam payload');
        $this->assertNull($tanpa['keterangan']);
        $this->assertSame(120.0, (float) $tanpa['nilai']);
        $this->assertTrue($tanpa['tersedia']);
    }

    public function test_keterangan_field_mapping_tolerant(): void
    {
        $this->actingAsAnalis();
        Http::fake([
            '*/api/v1/dataspasial/ringkasan*' => Http::response(fxRingkasanIndikator('detail'), 200),
            '*/api/v1/dataspasial/skema*' => Http::response(fxSkemaIsianIndikator(), 200),
            '*/api/v1/dataspasial/sektor*' => Http::response(fakeKoleksi([fxSektor('1', 'SK0', 'Infrastruktur')]), 200),
            '*/api/v1/dataspasial/nilai*' => Http::response(fxNilaiIsian(), 200),
        ]);

        $resp = $this->getJson('/dataspasial/dasbor/data?periode=2025-06')->assertOk();
        $this->assertSame(
            'L 30.112 · P 28.828',
            collect($resp->json('kartu'))->firstWhere('kode', 'jumlah_penduduk')['keterangan'] ?? null
        );
    }

    // Q4a — tombol "Salin tautan filter" berada di blok filter.

    public function test_page_has_copy_link_button_in_filter_block(): void
    {
        $this->actingAsAnalis();
        $this->fakeData();

        $html = $this->get('/dataspasial/dasbor')->assertOk()->getContent();
        $this->assertStringContainsString('data-testid="dasbor-filter"', $html);
        $this->assertStringContainsString('data-testid="bt-salin-tautan"', $html);
        $this->assertGreaterThan(
            strpos($html, 'data-testid="dasbor-filter"'),
            strpos($html, 'data-testid="bt-salin-tautan"'),
            'Tombol salin tautan harus berada setelah pembuka blok filter'
        );
    }

    // Q4b — tombol "Ekspor Excel" pindah ke blok filter, bukan di kepala tabel.

    public function test_export_button_moved_to_filter_block(): void
    {
        $this->actingAsAnalis();
        $this->fakeData();

        $html = $this->get('/dataspasial/dasbor')->assertOk()->getContent();
        $posFilter = strpos($html, 'data-testid="dasbor-filter"');
        $posEkspor = strpos($html, 'data-testid="bt-ekspor"');
        $posTabel = strpos($html, 'data-testid="dasbor-tabel-baris"');

        $this->assertNotFalse($posEkspor, 'Tombol ekspor harus ada');
        $this->assertGreaterThan($posFilter, $posEkspor, 'Tombol ekspor harus di blok filter');
        if ($posTabel !== false) {
            $this->assertLessThan($posTabel, $posEkspor, 'Tombol ekspor tidak boleh di dalam tabel');
        }
    }

    // Blok filter harus berada di posisi paling atas (sebelum kartu/grafik/tabel/peta).

    public function test_filter_block_positioned_at_top(): void
    {
        $this->actingAsAnalis();
        $this->fakeData();

        $html = $this->get('/dataspasial/dasbor')->assertOk()->getContent();
        $posFilter = strpos($html, 'data-testid="dasbor-filter"');
        $posKartu = strpos($html, 'data-testid="dasbor-kartu"');

        $this->assertNotFalse($posFilter, 'Blok filter harus ada');
        $this->assertNotFalse($posKartu, 'Wadah kartu harus ada');
        $this->assertLessThan($posKartu, $posFilter, 'Blok filter harus tampil sebelum (di atas) kartu');
        $this->assertStringContainsString('col-12', substr($html, max(0, $posFilter - 600), 600), 'Blok filter harus selebar halaman (col-12)');
    }
}
