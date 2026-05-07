<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ImageProxyController extends Controller
{
    /**
     * Proxy an image from an external URL with caching and validation.
     *
     * @param Request $request
     * @return Response
     */
    public function proxy(Request $request): Response
    {
        $url = $request->get('url');

        if (!$url || !filter_var($url, FILTER_VALIDATE_URL)) {
            Log::warning('ImageProxyController: Invalid URL provided', ['url' => $url]);
            abort(400, 'Invalid URL');
        }

        // Cache key berdasarkan URL
        $cacheKey = 'image_proxy_' . md5($url);

        // Cek cache
        $cachedImage = Cache::get($cacheKey);
        if ($cachedImage) {
            Log::info('ImageProxyController: Serving cached image', ['url' => $url]);
            return response($cachedImage['content'])->header('Content-Type', $cachedImage['content_type']);
        }

        Log::info('ImageProxyController: Fetching image from external URL', ['url' => $url]);
        $response = Http::timeout(10)->get($url);

        if (!$response->successful()) {
            Log::warning('ImageProxyController: External image request failed', [
                'url' => $url,
                'status' => $response->status()
            ]);
            abort(404, 'Image not found');
        }

        $content = $response->body();
        $contentType = $response->header('Content-Type') ?? '';

        // Pastikan ini gambar
        if (!str_starts_with($contentType, 'image/')) {
            Log::warning('ImageProxyController: URL does not point to an image', [
                'url' => $url,
                'content_type' => $contentType
            ]);
            abort(400, 'URL does not point to an image');
        }

        // Cache selama 1 jam
        Cache::put($cacheKey, ['content' => $content, 'content_type' => $contentType], 3600);
        Log::info('ImageProxyController: Image cached and served', ['url' => $url, 'content_type' => $contentType]);

        return response($content)->header('Content-Type', $contentType);
    }
}