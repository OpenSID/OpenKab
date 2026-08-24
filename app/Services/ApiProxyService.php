<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiProxyService
{
    protected $headers = [];

    protected $timeout;

    protected $cacheTtl;

    public function __construct()
    {
        $apiKey = Setting::where('key', 'database_gabungan_api_key')->first()?->value ?? '';

        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer '.$apiKey,
        ];

        $this->timeout = config('api_proxy.default_timeout', 30);
        $this->cacheTtl = config('api_proxy.cache_ttl', 3600);
    }

    /**
     * Get timeout value
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Make a GET request to the external API
     */
    public function get(string $endpoint, array $params = [], ?int $timeout = null): ?array
    {
        $useCache = ! empty($params['cache']) && filter_var($params['cache'], FILTER_VALIDATE_BOOLEAN);

        // Remove cache param from params for the actual API call
        $apiParams = $params;
        unset($apiParams['cache']);

        if ($useCache) {
            $cacheKey = $this->buildCacheKey($endpoint, $apiParams);
            $cached = Cache::get($cacheKey);

            if ($cached !== null) {
                return $cached;
            }
        }

        $response = $this->callApi('GET', $endpoint, $apiParams, null, $timeout ?? $this->timeout);

        if ($response === null) {
            return null;
        }

        if ($useCache) {
            Cache::put($cacheKey, $response, $this->cacheTtl);
        }

        return $response;
    }

    /**
     * Make a POST request to the external API
     */
    public function post(string $endpoint, array $body = [], ?int $timeout = null): ?array
    {
        $useCache = ! empty($body['cache']) && filter_var($body['cache'], FILTER_VALIDATE_BOOLEAN);

        // Remove cache param from body for the actual API call
        $apiBody = $body;
        unset($apiBody['cache']);

        if ($useCache) {
            $cacheKey = $this->buildCacheKey($endpoint, $apiBody);
            $cached = Cache::get($cacheKey);

            if ($cached !== null) {
                return $cached;
            }
        }

        $response = $this->callApi('POST', $endpoint, [], $apiBody, $timeout ?? $this->timeout);

        if ($response === null) {
            return null;
        }

        if ($useCache) {
            Cache::put($cacheKey, $response, $this->cacheTtl);
        }

        return $response;
    }

    /**
     * Clear cache for a specific endpoint
     */
    public function clearCache(string $endpoint, array $params = []): void
    {
        $cacheKey = $this->buildCacheKey($endpoint, $params);
        Cache::forget($cacheKey);
    }

    /**
     * Clear all cache
     */
    public function clearAllCache(): void
    {
        Cache::flush();
    }

    /**
     * Make the actual API call
     */
    protected function callApi(string $method, string $endpoint, array $params = [], ?array $body = null, ?int $timeout = null): ?array
    {
        $baseUrl = config('app.databaseGabunganUrl');
        $url = rtrim($baseUrl, '/').'/api/v1/'.ltrim($endpoint, '/');

        try {
            $http = Http::withHeaders($this->headers)
                ->timeout($timeout ?? $this->timeout);

            if ($method === 'GET') {
                $response = $http->get($url, $params)->throw();
            } else {
                $response = $http->post($url, $body ?? [])->throw();
            }

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('API Proxy: Exception', [
                'endpoint' => $endpoint,
                'url' => $url,
                'method' => $method,
                'file' => __FILE__,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Build cache key
     */
    protected function buildCacheKey(string $endpoint, array $params = []): string
    {
        return 'api_proxy_'.md5($endpoint.json_encode($params));
    }
}
