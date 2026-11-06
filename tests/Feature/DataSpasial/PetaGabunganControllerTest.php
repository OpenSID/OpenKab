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
        // Kategori tidak lagi menjadi layer terpisah (sudah tercakup grup geospasial lama).
        $this->assertNotContains('kategori', $jenis);
    }

    public function test_data_endpoint_exposes_filters_and_popup_from_skema(): void
    {
        $this->actingAsAnalis();
        $this->fakeGeoJson(fxGeoJsonGabungan()['features'], 200, true);

        $response = $this->getJson('/dataspasial/peta/data?sektor=SK0');

        // Fitur ke-3 (indeks 2) adalah lokasi SK0 dengan data jumlah_unit.
        $response->assertOk()
            ->assertJsonPath('meta.filters.0.kode', 'jenis_unit')
            ->assertJsonPath('features.2.properties.popup_isian.0.kode', 'jumlah_unit')
            ->assertJsonPath('features.2.properties.popup_isian.0.satuan', 'unit');

        // Hanya isian yang ditandai yang ikut (catatan tidak ditandai).
        $kodeFilter = array_column($response->json('meta.filters'), 'kode');
        $kodePopup = array_column($response->json('features.2.properties.popup_isian'), 'kode');

        $this->assertSame(['jenis_unit'], $kodeFilter);
        $this->assertSame(['jumlah_unit'], $kodePopup);
        $this->assertNotContains('catatan', array_merge($kodeFilter, $kodePopup));
    }

    public function test_popup_isian_tidak_tercampur_antar_sektor(): void
    {
        $this->actingAsAnalis();

        $features = [
            [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [115.2, -8.65]],
                'properties' => [
                    'id' => 1,
                    'nama' => 'Fasilitas Infrastruktur',
                    'sektor_kode' => 'SK0',
                    'sektor_nama' => 'Infrastruktur',
                    'dataspasial' => [
                        'isian_kode' => 'jumlah_unit',
                        'isian_label' => 'Jumlah Unit',
                        'isian_satuan' => 'unit',
                        'isian_tipe' => 'angka',
                        'nilai' => 12,
                        'periode' => '2026-11',
                    ],
                ],
            ],
            [
                'type' => 'Feature',
                'geometry' => ['type' => 'Point', 'coordinates' => [115.3, -8.66]],
                'properties' => [
                    'id' => 2,
                    'nama' => 'SDN 2',
                    'sektor_kode' => 'SK1',
                    'sektor_nama' => 'Pendidikan',
                    'dataspasial' => [
                        ['isian_kode' => 'jumlah_siswa', 'isian_tipe' => 'angka', 'nilai' => 233, 'periode' => '2026-11'],
                        ['isian_kode' => 'guru', 'isian_tipe' => 'angka', 'nilai' => 5, 'periode' => '2026-11'],
                    ],
                ],
            ],
        ];

        Http::fake([
            '*/api/v1/dataspasial/geojson*' => Http::response(['type' => 'FeatureCollection', 'features' => $features], 200),
            '*/api/v1/dataspasial/skema*' => function ($request) {
                $isian = str_contains($request->url(), 'SK1')
                    ? [
                        fxIsian('jumlah_siswa', ['label' => 'Jumlah Siswa', 'tipe' => 'angka', 'tampil_popup' => true]),
                        fxIsian('guru', ['label' => 'Guru', 'tipe' => 'angka', 'tampil_popup' => true]),
                    ]
                    : [
                        fxIsian('jumlah_unit', ['label' => 'Jumlah Unit', 'tipe' => 'angka', 'satuan' => 'unit', 'tampil_popup' => true]),
                    ];

                return Http::response(fakeKoleksi([
                    fxSkemaAktif(str_contains($request->url(), 'SK1') ? '2' : '1', 1, str_contains($request->url(), 'SK1') ? 'SK1' : 'SK0', 'Sektor', 1, 'terbit', $isian),
                ]), 200);
            },
        ]);

        $response = $this->getJson('/dataspasial/peta/data');

        $response->assertOk();
        $this->assertSame(['jumlah_unit'], array_column($response->json('features.0.properties.popup_isian'), 'kode'));
        $this->assertSame(['jumlah_siswa', 'guru'], array_column($response->json('features.1.properties.popup_isian'), 'kode'));
        $this->assertArrayNotHasKey('popup_isian', $response->json('meta'));
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
