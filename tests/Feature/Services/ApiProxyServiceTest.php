<?php

namespace Tests\Feature\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ApiProxyServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Set up config for api_proxy
        Config::set('api_proxy.default_timeout', 30);
        Config::set('api_proxy.cache_ttl', 3600);
        Config::set('app.databaseGabunganUrl', 'https://api.example.com');
    }

    /**
     * Test get method with no cache.
     */
    public function test_get_with_no_cache()
    {
        Http::fake([
            'https://api.example.com/api/v1/test/endpoint*' => Http::response(['data' => 'test'], 200),
        ]);

        $service = new \App\Services\ApiProxyService;
        $result = $service->get('test/endpoint', []);

        $this->assertEquals(['data' => 'test'], $result);
    }

    /**
     * Test get method with cache enabled returns cached data.
     */
    public function test_get_with_cache_enabled_returns_cached_data()
    {
        $cacheKey = 'api_proxy_'.md5('test/endpoint'.json_encode([]));
        Cache::put($cacheKey, ['cached' => 'data'], 3600);

        $service = new \App\Services\ApiProxyService;
        $result = $service->get('test/endpoint', ['cache' => true]);

        $this->assertEquals(['cached' => 'data'], $result);
    }

    /**
     * Test get method caches response when cache enabled.
     */
    public function test_get_caches_response_when_cache_enabled()
    {
        Http::fake([
            'https://api.example.com/api/v1/test/endpoint*' => Http::response(['data' => 'fresh'], 200),
        ]);

        $service = new \App\Services\ApiProxyService;
        $result = $service->get('test/endpoint', ['cache' => true]);

        $this->assertEquals(['data' => 'fresh'], $result);

        $cacheKey = 'api_proxy_'.md5('test/endpoint'.json_encode([]));
        $this->assertEquals(['data' => 'fresh'], Cache::get($cacheKey));
    }

    /**
     * Test get method with API failure returns null.
     */
    public function test_get_with_api_failure_returns_null()
    {
        Http::fake([
            'https://api.example.com/api/v1/test/endpoint*' => Http::response(null, 500),
        ]);

        $service = new \App\Services\ApiProxyService;
        $result = $service->get('test/endpoint', []);

        $this->assertNull($result);
    }

    /**
     * Test post method with no cache.
     */
    public function test_post_with_no_cache()
    {
        Http::fake([
            'https://api.example.com/api/v1/test/endpoint*' => Http::response(['result' => 'posted'], 200),
        ]);

        $service = new \App\Services\ApiProxyService;
        $result = $service->post('test/endpoint', ['key' => 'value']);

        $this->assertEquals(['result' => 'posted'], $result);
    }

    /**
     * Test post method with cache enabled returns cached data.
     */
    public function test_post_with_cache_enabled_returns_cached_data()
    {
        $cacheKey = 'api_proxy_'.md5('test/endpoint'.json_encode(['key' => 'value']));
        Cache::put($cacheKey, ['cached' => 'data'], 3600);

        $service = new \App\Services\ApiProxyService;
        $result = $service->post('test/endpoint', ['key' => 'value', 'cache' => true]);

        $this->assertEquals(['cached' => 'data'], $result);
    }

    /**
     * Test post method caches response when cache enabled.
     */
    public function test_post_caches_response_when_cache_enabled()
    {
        Http::fake([
            'https://api.example.com/api/v1/test/endpoint*' => Http::response(['data' => 'fresh'], 200),
        ]);

        $service = new \App\Services\ApiProxyService;
        $result = $service->post('test/endpoint', ['key' => 'value', 'cache' => true]);

        $this->assertEquals(['data' => 'fresh'], $result);

        $cacheKey = 'api_proxy_'.md5('test/endpoint'.json_encode(['key' => 'value']));
        $this->assertEquals(['data' => 'fresh'], Cache::get($cacheKey));
    }

    /**
     * Test post method with API failure returns null.
     */
    public function test_post_with_api_failure_returns_null()
    {
        Http::fake([
            'https://api.example.com/api/v1/test/endpoint*' => Http::response(null, 500),
        ]);

        $service = new \App\Services\ApiProxyService;
        $result = $service->post('test/endpoint', ['key' => 'value']);

        $this->assertNull($result);
    }

    /**
     * Test clearCache method removes specific cache entry.
     */
    public function test_clear_cache_removes_specific_entry()
    {
        $cacheKey = 'api_proxy_'.md5('test/endpoint'.json_encode([]));
        Cache::put($cacheKey, 'test-data', 3600);

        $service = new \App\Services\ApiProxyService;
        $service->clearCache('test/endpoint', []);

        $this->assertNull(Cache::get($cacheKey));
    }

    /**
     * Test clearAllCache method removes all cache entries.
     */
    public function test_clear_all_cache_removes_all_entries()
    {
        Cache::put('key1', 'data1', 3600);
        Cache::put('key2', 'data2', 3600);

        $service = new \App\Services\ApiProxyService;
        $service->clearAllCache();

        $this->assertNull(Cache::get('key1'));
        $this->assertNull(Cache::get('key2'));
    }

    /**
     * Test that cache key generation works correctly through get method.
     */
    public function test_cache_key_generation_works_correctly()
    {
        Http::fake([
            'https://api.example.com/api/v1/test/endpoint*' => Http::response(['data' => 'fresh'], 200),
        ]);

        $service = new \App\Services\ApiProxyService;

        // First call with cache - should hit API
        $result1 = $service->get('test/endpoint', ['cache' => true, 'param' => 'value1']);

        // Second call with same params - should hit cache
        $result2 = $service->get('test/endpoint', ['cache' => true, 'param' => 'value1']);

        // Third call with different params - should hit API again
        $result3 = $service->get('test/endpoint', ['cache' => true, 'param' => 'value2']);

        $this->assertEquals(['data' => 'fresh'], $result1);
        $this->assertEquals(['data' => 'fresh'], $result2); // Should be same as first (cached)
        $this->assertEquals(['data' => 'fresh'], $result3); // Should be same data (different cache key)

        // Verify that we made 2 HTTP calls (first and third, second was cached)
        Http::assertSentCount(2);
    }

    /**
     * Test getTimeout method returns correct timeout value.
     */
    public function test_get_timeout_returns_correct_value()
    {
        $service = new \App\Services\ApiProxyService;
        $timeout = $service->getTimeout();

        $this->assertEquals(30, $timeout);
    }

    /**
     * Test that error is logged when API call fails.
     */
    public function test_error_is_logged_when_api_call_fails()
    {
        Http::fake([
            'https://api.example.com/api/v1/test/endpoint*' => Http::response(null, 500),
        ]);

        // Mock the Log facade to expect an error call
        Log::shouldReceive('error')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'API Proxy: Exception') &&
                       isset($context['endpoint']) &&
                       $context['endpoint'] === 'test/endpoint';
            });

        $service = new \App\Services\ApiProxyService;
        $result = $service->get('test/endpoint', []);

        $this->assertNull($result);
    }
}
