<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageProxyController extends Controller
{
    private const MAX_CACHE_BYTES = 1048576; // 1MB default, configurable

    public function proxy(Request $request): Response
    {
        $url = $request->get('url');

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            Log::warning('ImageProxyController: Invalid URL provided', ['url' => $url]);
            abort(400, 'Invalid URL');
        }

        $parsed = parse_url($url);
        $host = $parsed['host'] ?? null;

        if (!$host) {
            Log::warning('ImageProxyController: Invalid host', ['url' => $url]);
            abort(400, 'Invalid host');
        }        

        $cacheKey = 'image_proxy_' . md5($url);

        $cachedImage = Cache::get($cacheKey);
        if ($cachedImage) {
            Log::info('ImageProxyController: Serving cached image', ['url' => $url]);
            return response($cachedImage['content'])->header('Content-Type', $cachedImage['content_type']);
        }

        $timeout = config('image_proxy.default_timeout', 10);
        try {
            $response = Http::timeout($timeout)->get($url);
        } catch (\Exception $e) {
            Log::warning('ImageProxyController: External image request failed', [
                'url' => $url,
                'error' => $e->getMessage()
            ]);
            abort(404, 'Image not found');
        }

        if (!$response->successful()) {
            Log::warning('ImageProxyController: External image request failed', [
                'url' => $url,
                'status' => $response->status()
            ]);
            abort(404, 'Image not found');
        }

        $content = $response->body();
        $contentType = $response->header('Content-Type') ?? '';

        if (!str_starts_with($contentType, 'image/')) {
            Log::warning('ImageProxyController: URL does not point to an image', [
                'url' => $url,
                'content_type' => $contentType
            ]);
            abort(400, 'URL does not point to an image');
        }

        $maxCacheBytes = config('image_proxy.max_cache_bytes', self::MAX_CACHE_BYTES);
        $bytes = strlen($content);

        if ($bytes <= $maxCacheBytes) {
            Cache::put($cacheKey, ['content' => $content, 'content_type' => $contentType], config('image_proxy.cache_ttl', 3600));
            Log::info('ImageProxyController: Image cached and served', ['url' => $url, 'content_type' => $contentType, 'bytes' => $bytes]);
        } else {
            Log::warning('ImageProxyController: Skipping cache, payload too large', ['url' => $url, 'bytes' => $bytes, 'max' => $maxCacheBytes]);
        }

        return response($content)->header('Content-Type', $contentType);
    }
}