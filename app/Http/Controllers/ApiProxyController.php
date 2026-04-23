<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiProxyController extends Controller
{
    protected array $headers = [];

    protected int $timeout;

    protected int $cacheTtl;

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

    public function get(Request $request): JsonResponse
    {
        $endpoint = $this->resolveEndpoint($request->get('endpoint'));
        $params = $request->query->all();

        unset($params['endpoint'], $params['cache']);

        if (! $endpoint) {
            return response()->json(['error' => 'Endpoint tidak boleh kosong'], 400);
        }

        $useCache = filter_var($request->get('cache', false), FILTER_VALIDATE_BOOLEAN);

        if ($useCache) {
            $cacheKey = $this->buildCacheKey($endpoint, $params);
            $cached = Cache::get($cacheKey);

            if ($cached !== null) {
                return response()->json($cached);
            }
        }

        $response = $this->callApi('GET', $endpoint, $params);

        if ($response === null) {
            return response()->json(['error' => 'Gagal mengambil data dari API'], 500);
        }

        if ($useCache) {
            Cache::put($cacheKey, $response, $this->cacheTtl);
        }

        return response()->json($response);
    }

    public function post(Request $request): JsonResponse
    {
        $endpoint = $this->resolveEndpoint($request->get('endpoint'));
        $body = $request->except('endpoint');

        unset($body['endpoint']);

        if (! $endpoint) {
            return response()->json(['error' => 'Endpoint tidak boleh kosong'], 400);
        }

        $response = $this->callApi('POST', $endpoint, [], $body);

        if ($response === null) {
            return response()->json(['error' => 'Gagal mengirim data ke API'], 500);
        }

        return response()->json($response);
    }

    protected function resolveEndpoint(?string $key): ?string
    {
        if (! $key) {
            return null;
        }

        $endpoints = config('api_proxy.endpoints', []);

        return $endpoints[$key] ?? $key;
    }

    protected function callApi(string $method, string $endpoint, array $params = [], ?array $body = null): ?array
    {
        $baseUrl = config('app.databaseGabunganUrl');
        $url = rtrim($baseUrl, '/').'/api/v1/'.ltrim($endpoint, '/');

        try {
            $http = Http::withHeaders($this->headers)
                ->timeout($this->timeout);

            if ($method === 'GET') {
                $response = $http->get($url, $params)->throw();
            } else {
                $response = $http->post($url, $body ?? [])->throw();
            }

            if ($response->successful()) {
                return $response->json();
            }                    
        } catch (\Exception $e) {
            Log::error('API Proxy: Exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function buildCacheKey(string $endpoint, array $params = []): string
    {
        return 'api_proxy_'.md5($endpoint.json_encode($params));
    }

    public function clearCache(Request $request): JsonResponse
    {
        $endpoint = $request->get('endpoint');

        if ($endpoint) {
            $cacheKey = $this->buildCacheKey($this->resolveEndpoint($endpoint));
            Cache::forget($cacheKey);
        } else {
            Cache::flush();
        }

        return response()->json(['message' => 'Cache cleared']);
    }
}
