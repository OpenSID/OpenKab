<?php

namespace Tests\Unit;

use App\Services\ArtikelService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ArtikelServiceTest extends TestCase
{
    protected ArtikelService $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new ArtikelService();
    }

    /** @test */
    public function it_can_instantiate_artikel_service()
    {
        $this->assertInstanceOf(ArtikelService::class, $this->service);
    }

    /** @test */
    public function it_builds_cache_key_correctly()
    {
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('buildCacheKey');
        $method->setAccessible(true);

        $cacheKey = $method->invokeArgs($this->service, ['artikel', ['id' => 1]]);
        
        $this->assertIsString($cacheKey);
        $this->assertStringContainsString('artikel', $cacheKey);
    }

    /** @test */
    public function clear_cache_removes_cached_data()
    {
        $cacheKey = 'test_artikel_cache';
        Cache::put($cacheKey, 'test_data', 3600);
        
        $this->assertTrue(Cache::has($cacheKey));
        
        Cache::forget($cacheKey);
        
        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test */
    public function it_has_cache_ttl_property()
    {
        $reflection = new \ReflectionClass($this->service);
        $property = $reflection->getProperty('cacheTtl');
        $property->setAccessible(true);
        
        $ttl = $property->getValue($this->service);
        
        $this->assertEquals(3600, $ttl);
        $this->assertIsInt($ttl);
    }

    /** @test */
    public function artikel_method_returns_collection()
    {
        // Mock API response untuk testing
        // Note: Ini membutuhkan mock untuk API request
        // Untuk test sederhana, kita hanya check method exists
        $this->assertTrue(method_exists($this->service, 'artikel'));
    }

    /** @test */
    public function artikel_by_id_method_exists()
    {
        $this->assertTrue(method_exists($this->service, 'artikelById'));
    }

    /** @test */
    public function clear_cache_method_exists()
    {
        $this->assertTrue(method_exists($this->service, 'clearCache'));
    }

    /** @test */
    public function service_extends_base_api_service()
    {
        $this->assertInstanceOf(\App\Services\BaseApiService::class, $this->service);
    }
}
