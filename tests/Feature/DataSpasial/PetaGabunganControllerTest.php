<?php

declare(strict_types=1);

namespace Tests\Feature\DataSpasial;

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PetaGabunganControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.databaseGabunganUrl' => 'https://api.example.test']);
        config(['dataspasial.cache_ttl' => 300]);

        Setting::query()->updateOrCreate(
            ['key' => 'database_gabungan_api_key'],
            ['value' => 'test-token']
        );

        Cache::flush();
    }

    private function actingAsAnalis(): void
    {
        $this->actingAs(makeRole('analis opd', ['dataspasial-peta-read']));
    }

    /**
     * @param  array<int, array<string, mixed>>  $features
     */
    private function fakeGeoJson(array $features, int $status = 200, bool $withSkema = false): void
    {
        $fakes = [
            '*/api/v1/dataspasial/geojson*' => Http::response([
                'type' => 'FeatureCollection',
                'features' => $features,
            ], $status),
        ];

        if ($withSkema) {
            $fakes['*/api/v1/dataspasial/skema*'] = Http::response(fxSkemaIsianFilter(), 200);
        }

        Http::fake($fakes);
    }

    public function test_guest_is_redirected_from_peta(): void
    {
        $this->get('/dataspasial/peta')->assertRedirect('/login');
    }

    public function test_page_renders_two_block_layout(): void
    {
        $this->actingAsAnalis();
        $this->fakeGeoJson(fxGeoJsonGabungan()['features']);

        $this->get('/dataspasial/peta')
            ->assertOk()
            ->assertSee('Peta Gabungan')
            ->assertSee('data-testid="filter-kecamatan"', false)
            ->assertSee('data-testid="map-layers"', false)
            ->assertSee('id="map"', false);
    }

    public function test_page_has_no_sector_picker(): void
    {
        $this->actingAsAnalis();
        $this->fakeGeoJson(fxGeoJsonGabungan()['features']);

        $this->get('/dataspasial/peta')->assertOk()->assertDontSee('filter_sektor');
    }

    public function test_data_endpoint_returns_featurecollection_with_layers(): void
    {
        $this->actingAsAnalis();
        $this->fakeGeoJson(fxGeoJsonGabungan()['features']);

        $response = $this->getJson('/dataspasial/peta/data');

        $response->assertOk()
            ->assertJsonPath('type', 'FeatureCollection')
            ->assertJsonCount(6, 'features')
            ->assertJsonPath('meta.unavailable', false)
            ->assertJsonPath('meta.empty', false);

        $layers = $response->json('meta.layers');
        $jenis = array_column($layers, 'jenis');
        $this->assertContains('geospasial_lama', $jenis);
        $this->assertContains('dataspasial', $jenis);
    }

    public function test_data_endpoint_exposes_filters_popup_and_thematic_from_skema(): void
    {
        $this->actingAsAnalis();
        $this->fakeGeoJson(fxGeoJsonGabungan()['features'], 200, true);

        $response = $this->getJson('/dataspasial/peta/data?sektor=SK0');

        $response->assertOk()
            ->assertJsonPath('meta.filters.0.kode', 'jenis_unit')
            ->assertJsonPath('meta.popup_isian.0.kode', 'jumlah_unit')
            ->assertJsonPath('meta.popup_isian.0.satuan', 'unit')
            ->assertJsonPath('meta.isian_tematik_options.0.value', 'jumlah_unit');

        // Hanya isian yang ditandai yang ikut (catatan tidak ditandai).
        $kodeFilter = array_column($response->json('meta.filters'), 'kode');
        $kodePopup = array_column($response->json('meta.popup_isian'), 'kode');
        $kodeTematik = array_column($response->json('meta.isian_tematik_options'), 'value');

        $this->assertSame(['jenis_unit'], $kodeFilter);
        $this->assertSame(['jumlah_unit'], $kodePopup);
        $this->assertSame(['jumlah_unit'], $kodeTematik);
        $this->assertNotContains('catatan', array_merge($kodeFilter, $kodePopup, $kodeTematik));
    }

    public function test_data_endpoint_flags_unavailable_without_error(): void
    {
        $this->actingAsAnalis();
        $this->fakeGeoJson([], 503);

        $this->getJson('/dataspasial/peta/data')
            ->assertOk()
            ->assertJsonPath('meta.unavailable', true)
            ->assertJsonCount(0, 'features');
    }

    public function test_data_endpoint_flags_empty_when_no_features(): void
    {
        $this->actingAsAnalis();
        $this->fakeGeoJson([]);

        $this->getJson('/dataspasial/peta/data')
            ->assertOk()
            ->assertJsonPath('meta.empty', true);
    }

    public function test_data_endpoint_applies_isian_filter(): void
    {
        $this->actingAsAnalis();
        $features = fxGeoJsonGabungan()['features'];
        $this->fakeGeoJson($features);

        // Hanya satu fitur yang `dataspasial.isian_kode = jumlah_unit` dengan nilai 12.
        $response = $this->getJson('/dataspasial/peta/data?sektor=SK0&filter[jumlah_unit]=12');
        $response->assertOk();

        $points = array_filter(
            $response->json('features'),
            static fn (array $f): bool => ($f['geometry']['type'] ?? null) === 'Point'
        );
        $this->assertCount(1, $points);
    }

    public function test_data_endpoint_flags_empty_when_filter_excludes_all(): void
    {
        $this->actingAsAnalis();
        $this->fakeGeoJson(fxGeoJsonGabungan()['features']);

        $this->getJson('/dataspasial/peta/data?filter[jumlah_unit]=999999')
            ->assertOk()
            ->assertJsonPath('meta.empty', true);
    }

    public function test_empty_featurecollection_is_flagged_empty(): void
    {
        $this->actingAsAnalis();
        $this->fakeGeoJson([]);

        $this->getJson('/dataspasial/peta/data')
            ->assertOk()
            ->assertJsonPath('meta.empty', true);
    }

    public function test_data_preserves_polygon_without_value_as_no_data(): void
    {
        $this->actingAsAnalis();
        $this->fakeGeoJson(fxGeoJsonGabungan()['features']);

        $response = $this->getJson('/dataspasial/peta/data');
        $response->assertOk();

        $polygons = array_values(array_filter(
            $response->json('features'),
            static fn (array $f): bool => ($f['geometry']['type'] ?? null) === 'Polygon'
        ));

        $this->assertCount(3, $polygons);

        $nilai = array_map(
            static fn (array $f): mixed => $f['properties']['nilai'] ?? null,
            $polygons
        );

        // Nilai tetap apa adanya: nol tetap nol, nilai positif tetap positif.
        $this->assertContains(0, $nilai);
        $this->assertContains(5, $nilai);

        // Desa tanpa data TIDAK dipaksa menjadi nol (tidak ada kunci nilai).
        $tanpaData = array_values(array_filter(
            $polygons,
            static fn (array $f): bool => ($f['properties']['nama_desa'] ?? '') === 'Desa Tanpa Data'
        ));
        $this->assertCount(1, $tanpaData);
        $this->assertArrayNotHasKey('nilai', $tanpaData[0]['properties']);
    }

    public function test_data_forwards_kecamatan_filter_to_api(): void
    {
        $this->actingAsAnalis();
        $this->fakeGeoJson(fxGeoJsonGabungan()['features']);

        $this->getJson('/dataspasial/peta/data?kode_kecamatan=510201')->assertOk();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/dataspasial/geojson')
                && str_contains($request->url(), 'kode_kecamatan=510201');
        });
    }
}
