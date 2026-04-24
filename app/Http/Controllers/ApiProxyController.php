<?php

namespace App\Http\Controllers;

use App\Services\ApiProxyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiProxyController extends Controller
{
    protected ApiProxyService $apiProxyService;

    public function __construct(ApiProxyService $apiProxyService)
    {
        $this->apiProxyService = $apiProxyService;
    }

    public function get(Request $request): JsonResponse
    {
        $endpoint = $this->resolveEndpoint($request->get('endpoint'));
        $params = $request->query->all();

        unset($params['endpoint'], $params['cache'], $params['timeout']);

        if (! $endpoint) {
            return response()->json(['error' => 'Endpoint tidak boleh kosong'], 400);
        }

        $useCache = filter_var($request->get('cache', false), FILTER_VALIDATE_BOOLEAN);
        $timeout = $request->get('timeout', $this->apiProxyService->getTimeout());

        // Add cache parameter to params for the service
        if ($useCache) {
            $params['cache'] = true;
        }

        $response = $this->apiProxyService->get($endpoint, $params, $timeout);

        if ($response === null) {
            return response()->json(['error' => 'Gagal mengambil data dari API'], 500);
        }

        return response()->json($response);
    }

    public function post(Request $request): JsonResponse
    {
        $endpoint = $this->resolveEndpoint($request->get('endpoint'));
        $body = $request->except('endpoint');

        // Extract proxy parameters before removing them
        $cacheParam = $body['cache'] ?? false;
        $timeoutParam = $body['timeout'] ?? null;

        // Remove proxy parameters from body so they are not sent to the API
        unset($body['endpoint'], $body['cache'], $body['timeout']);

        if (! $endpoint) {
            return response()->json(['error' => 'Endpoint tidak boleh kosong'], 400);
        }

        $useCache = filter_var($cacheParam, FILTER_VALIDATE_BOOLEAN);
        $timeout = $timeoutParam ?? $this->apiProxyService->getTimeout();

        // Add cache parameter to body for the service
        if ($useCache) {
            $body['cache'] = true;
        }

        $response = $this->apiProxyService->post($endpoint, $body, $timeout);

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

    public function clearCache(Request $request): JsonResponse
    {
        $endpoint = $request->get('endpoint');

        if ($endpoint) {
            $this->apiProxyService->clearCache($this->resolveEndpoint($endpoint), []);
        } else {
            $this->apiProxyService->clearAllCache();
        }

        return response()->json(['message' => 'Cache cleared']);
    }
}
