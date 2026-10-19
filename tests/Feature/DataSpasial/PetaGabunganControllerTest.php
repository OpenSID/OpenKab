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

    protected function makePetaUser(): void
    {
        $user = makeRole('analis opd', ['dataspasial-peta-read']);
        $this->actingAs($user);
    }

    private function fakeGeoJson(array $features = [], int $status = 200): void
    {
        Http::fake([
            '*/api/v1/config/*' => Http::response([], 200),
            '*/api/v1/dataspasial/geojson*' => Http::response([
                'type' => 'FeatureCollection',
                'features' => $features,
            ], $status),
        ]);
    }

    public function test_can_access_peta_page(): void
    {
        $user = makeRole('analis opd', ['dataspasial-peta-read']);
        $this->actingAs($user);

        Http::fake([
            '*/api/v1/dataspasial/geojson*' => Http::response([
                'type' => 'FeatureCollection',
                'features' => [
                    [
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Point', 'coordinates' => [115.2, -8.65]],
                        'properties' => ['id' => 1, 'nama' => 'Fasilitas Uji'],
                    ],
                ],
            ], 200),
            '*/api/v1/dataspasial/sektor*' => Http::response(fakeKoleksi([]), 200),
        ]);

        $this->get('/dataspasial/peta')
            ->assertStatus(200)
            ->assertSee('Peta Gabungan');
    }

    public function test_data_endpoint_returns_featurecollection(): void
    {
        $this->actingAs(makeRole('analis opd', ['dataspasial-peta-read']));

        Http::fake([
            '*/api/v1/dataspasial/geojson*' => Http::response([
                'type' => 'FeatureCollection',
                'features' => [
                    [
                        'type' => 'Feature',
                        'geometry' => ['type' => 'Point', 'coordinates' => [115.2, -8.65]],
                        'properties' => [
                            'id' => 1,
                            'nama' => 'Test Fasilitas',
                            'dataspasial' => ['nilai' => 12, 'periode' => '2025-06', 'isian_kode' => 'jumlah_siswa'],
                        ],
                    ],
                ],
            ], 200),
        ]);

        $response = $this->getJson('/dataspasial/peta/data');

        $response->assertOk()
            ->assertJsonPath('type', 'FeatureCollection')
            ->assertJsonCount(1, 'features')
            ->assertJsonPath('meta.periods.0', '2025-06')
            ->assertJsonPath('meta.isian_tematik_options.0.value', 'jumlah_siswa');
    }

    public function test_data_endpoint_flags_unavailable_without_error(): void
    {
        $this->actingAs(makeRole('analis opd', ['dataspasial-peta-read']));

        Http::fake([
            '*/api/v1/dataspasial/geojson*' => Http::response(['message' => 'Service Unavailable'], 503),
        ]);

        $response = $this->getJson('/dataspasial/peta/data');

        $response->assertOk()
            ->assertJsonPath('meta.unavailable', true)
            ->assertJsonCount(0, 'features');
    }

    public function test_data_endpoint_flags_empty(): void
    {
        $this->actingAs(makeRole('analis opd', ['dataspasial-peta-read']));

        Http::fake([
            '*/api/v1/dataspasial/geojson*' => Http::response([
                'type' => 'FeatureCollection',
                'features' => [],
            ], 200),
        ]);

        $response = $this->getJson('/dataspasial/peta/data');

        $response->assertOk()
            ->assertJsonPath('meta.empty', true);
    }

    public function test_guest_is_redirected_from_peta(): void
    {
        $this->get('/dataspasial/peta')->assertRedirect('/login');
    }
}
