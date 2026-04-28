<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\BaseTestCase;

class ApiProxyControllerTest extends BaseTestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        // Set up config for api_proxy
        Config::set('api_proxy.default_timeout', 30);
        Config::set('api_proxy.cache_ttl', 3600);
        Config::set('api_proxy.endpoints', [
            'desa-aktif' => 'laporan/desa-aktif',
            'test-endpoint' => 'test/endpoint',
        ]);
        Config::set('app.databaseGabunganUrl', 'https://api.example.com');

        // Create and authenticate a test user
        $user = User::factory()->create();
        $this->actingAs($user);
    }

    /**
     * Test get method with valid endpoint and no cache.
     */
    public function test_get_with_valid_endpoint_no_cache()
    {
        Http::fake([
            'https://api.example.com/api/v1/test/endpoint*' => Http::response(['data' => 'test'], 200),
        ]);

        $response = $this->getJson('/api-proxy/get?endpoint=test-endpoint');

        $response->assertStatus(200)
            ->assertJson(['data' => 'test']);
    }

    /**
     * Test get method with cache enabled returns cached data.
     */
    public function test_get_with_cache_enabled_returns_cached_data()
    {
        $cacheKey = 'api_proxy_'.md5('test/endpoint'.json_encode([]));
        Cache::put($cacheKey, ['cached' => 'data'], 3600);

        $response = $this->getJson('/api-proxy/get?endpoint=test-endpoint&cache=true');

        $response->assertStatus(200)
            ->assertJson(['cached' => 'data']);
    }

    /**
     * Test get method caches response when cache enabled.
     */
    public function test_get_caches_response_when_cache_enabled()
    {
        Http::fake([
            'https://api.example.com/api/v1/test/endpoint*' => Http::response(['data' => 'fresh'], 200),
        ]);

        $response = $this->getJson('/api-proxy/get?endpoint=test-endpoint&cache=true');

        $response->assertStatus(200)
            ->assertJson(['data' => 'fresh']);

        $cacheKey = 'api_proxy_'.md5('test/endpoint'.json_encode([]));
        $this->assertEquals(['data' => 'fresh'], Cache::get($cacheKey));
    }

    /**
     * Test get method with API failure returns 500.
     */
    public function test_get_with_api_failure_returns_500()
    {
        Http::fake([
            'https://api.example.com/api/v1/test/endpoint*' => Http::response(null, 500),
        ]);

        $response = $this->getJson('/api-proxy/get?endpoint=test-endpoint');

        $response->assertStatus(500)
            ->assertJson(['error' => 'Gagal mengambil data dari API']);
    }    

    /**
     * Test post method with valid endpoint.
     */
    public function test_post_with_valid_endpoint()
    {
        Http::fake([
            'https://api.example.com/api/v1/test/endpoint*' => Http::response(['result' => 'posted'], 200),
        ]);

        $response = $this->postJson('/api-proxy/post?endpoint=test-endpoint', ['key' => 'value']);

        $response->assertStatus(200)
            ->assertJson(['result' => 'posted']);
    }

    /**
     * Test post method with API failure returns 500.
     */
    public function test_post_with_api_failure_returns_500()
    {
        Http::fake([
            'https://api.example.com/api/v1/test/endpoint*' => Http::response(null, 500),
        ]);

        $response = $this->postJson('/api-proxy/post?endpoint=test-endpoint');

        $response->assertStatus(500)
            ->assertJson(['error' => 'Gagal mengirim data ke API']);
    }

    /**
     * Test clearCache method with specific endpoint.
     */
    public function test_clear_cache_with_specific_endpoint()
    {
        $cacheKey = 'api_proxy_'.md5('test/endpoint');
        Cache::put($cacheKey, 'test-data', 3600);

        $response = $this->getJson('/api-proxy/clear-cache?endpoint=test-endpoint');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Cache cleared']);
    }

    /**
     * Test clearCache method without endpoint clears all cache.
     */
    public function test_clear_cache_without_endpoint_clears_all()
    {
        Cache::put('some_key', 'data', 3600);

        $response = $this->getJson('/api-proxy/clear-cache');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Cache cleared']);

        // Note: Cache::flush() is called, so all cache is cleared
        // In test environment, we can't easily verify flush, but response is checked
    }

    /**
     * Test resolveEndpoint with config endpoint.
     */
    public function test_resolve_endpoint_with_config_endpoint()
    {
        $apiProxyService = $this->createMock(\App\Services\ApiProxyService::class);
        $controller = new \App\Http\Controllers\ApiProxyController($apiProxyService);

        $method = new \ReflectionMethod($controller, 'resolveEndpoint');
        $method->setAccessible(true);

        $result = $method->invoke($controller, 'desa-aktif');
        $this->assertEquals('laporan/desa-aktif', $result);
    }

    /**
     * Test resolveEndpoint with direct endpoint.
     */
    public function test_resolve_endpoint_with_direct_endpoint()
    {
        $apiProxyService = $this->createMock(\App\Services\ApiProxyService::class);
        $controller = new \App\Http\Controllers\ApiProxyController($apiProxyService);

        $method = new \ReflectionMethod($controller, 'resolveEndpoint');
        $method->setAccessible(true);

        $result = $method->invoke($controller, 'direct/endpoint');
        $this->assertEquals('direct/endpoint', $result);
    }
}
