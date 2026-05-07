<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\BaseTestCase;

class ImageProxyControllerTest extends BaseTestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        // Clear cache before each test
        Cache::flush();
        // Spy on Log facade to allow logging without strict expectations
        Log::spy();
    }

    /**
     * Test proxy with invalid URL.
     */
    public function test_proxy_with_invalid_url()
    {
        $response = $this->get('/image-proxy?url=invalid-url');

        $response->assertStatus(400);
    }

    /**
     * Test proxy with empty URL.
     */
    public function test_proxy_with_empty_url()
    {
        $response = $this->get('/image-proxy');

        $response->assertStatus(400);
    }

    /**
     * Test proxy with valid URL but non-image content type.
     */
    public function test_proxy_with_non_image_content()
    {
        $url = 'https://example.com/image.jpg';

        Http::fake([
            $url => Http::response('not an image', 200, ['Content-Type' => 'text/html']),
        ]);

        $response = $this->get('/image-proxy?url=' . urlencode($url));

        $response->assertStatus(400);
    }

    /**
     * Test proxy with successful image fetch.
     */
    public function test_proxy_with_successful_image_fetch()
    {
        $url = 'https://example.com/image.jpg';
        $imageContent = 'fake image data';
        $contentType = 'image/jpeg';

        Http::fake([
            $url => Http::response($imageContent, 200, ['Content-Type' => $contentType]),
        ]);

        $response = $this->get('/image-proxy?url=' . urlencode($url));

        $response->assertStatus(200)
            ->assertHeader('Content-Type', $contentType);

        $this->assertEquals($imageContent, $response->getContent());
    }

    /**
     * Test proxy serves from cache.
     */
    public function test_proxy_serves_from_cache()
    {
        $url = 'https://example.com/image.jpg';
        $imageContent = 'cached image data';
        $contentType = 'image/png';
        $cacheKey = 'image_proxy_' . md5($url);

        // Manually set cache
        Cache::put($cacheKey, ['content' => $imageContent, 'content_type' => $contentType], 3600);

        $response = $this->get('/image-proxy?url=' . urlencode($url));

        $response->assertStatus(200)
            ->assertHeader('Content-Type', $contentType);

        $this->assertEquals($imageContent, $response->getContent());
    }

    /**
     * Test proxy with failed external request.
     */
    public function test_proxy_with_failed_external_request()
    {
        $url = 'https://example.com/image.jpg';

        Http::fake([
            $url => Http::response(null, 404),
        ]);

        $response = $this->get('/image-proxy?url=' . urlencode($url));

        $response->assertStatus(404);
    }

    /**
     * Test proxy with exception during fetch.
     */
    public function test_proxy_with_exception_during_fetch()
    {
        $url = 'https://example.com/image.jpg';

        Http::fake([
            $url => function () {
                throw new \Exception('Network error');
            },
        ]);

        $response = $this->get('/image-proxy?url=' . urlencode($url));

        $response->assertStatus(404);
    }
}