<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class GlobalRateLimiterTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear cache before each test
        Cache::flush();
    }

    /** @test */
    public function it_allows_requests_when_rate_limiter_is_disabled()
    {
        // Disable rate limiter
        Config::set('rate-limiter.enabled', false);

        // Make multiple requests to homepage
        for ($i = 0; $i < 10; $i++) {
            $response = $this->get('/');
            
            $this->assertNotEquals(429, $response->getStatusCode());
        }
    }

    /** @test */
    public function it_allows_requests_within_limit_when_rate_limiter_is_enabled()
    {
        // Enable rate limiter with low limits for testing
        Config::set('rate-limiter.enabled', true);
        Config::set('rate-limiter.max_attempts', 5);
        Config::set('rate-limiter.decay_minutes', 1);

        // Make requests within limit
        for ($i = 0; $i < 5; $i++) {
            $response = $this->get('/');
            
            $this->assertNotEquals(429, $response->getStatusCode());
            $this->assertTrue($response->headers->has('X-RateLimit-Limit'));
            $this->assertTrue($response->headers->has('X-RateLimit-Remaining'));
            $this->assertTrue($response->headers->has('X-RateLimit-Reset'));
        }
    }

    /** @test */
    public function it_blocks_requests_when_limit_is_exceeded()
    {
        // Enable rate limiter with low limits for testing
        Config::set('rate-limiter.enabled', true);
        Config::set('rate-limiter.max_attempts', 2);
        Config::set('rate-limiter.decay_minutes', 1);

        // Make requests within the limit
        $response1 = $this->get('/');
        $this->assertNotEquals(429, $response1->getStatusCode());

        $response2 = $this->get('/');
        $this->assertNotEquals(429, $response2->getStatusCode());

        // The next request should be blocked
        $response3 = $this->get('/');
        $this->assertEquals(429, $response3->getStatusCode());
    }

    /** @test */
    public function it_returns_correct_json_response_for_api_requests()
    {
        // Enable rate limiter with low limits for testing
        Config::set('rate-limiter.enabled', true);
        Config::set('rate-limiter.max_attempts', 1);
        Config::set('rate-limiter.decay_minutes', 1);

        // Make a request to exceed the limit
        $this->get('/api/user');
        
        // The next request should be blocked with JSON response
        $response = $this->get('/api/user', ['Accept' => 'application/json']);
        
        $this->assertEquals(429, $response->getStatusCode());
        $response->assertJson([
            'message' => 'Too many requests. Please try again later.',
            'status' => 'error',
            'code' => 429,
        ]);
        $response->assertJsonStructure(['retry_after']);
    }

    /** @test */
    public function it_excludes_configured_paths_from_rate_limiting()
    {
        // Enable rate limiter with low limits
        Config::set('rate-limiter.enabled', true);
        Config::set('rate-limiter.max_attempts', 1);
        Config::set('rate-limiter.decay_minutes', 1);
        
        // Exclude a specific path
        Config::set('rate-limiter.exclude_paths', ['search']);

        // Make multiple requests to excluded path
        for ($i = 0; $i < 5; $i++) {
            $response = $this->get('/search');
            
            $this->assertNotEquals(429, $response->getStatusCode());
        }
    }

    /** @test */
    public function it_excludes_configured_ip_addresses_from_rate_limiting()
    {
        // Enable rate limiter with low limits
        Config::set('rate-limiter.enabled', true);
        Config::set('rate-limiter.max_attempts', 1);
        Config::set('rate-limiter.decay_minutes', 1);
        
        // Exclude localhost IP
        Config::set('rate-limiter.exclude_ips', ['127.0.0.1']);

        // Make multiple requests from excluded IP
        for ($i = 0; $i < 5; $i++) {
            $response = $this->get('/');
            
            $this->assertNotEquals(429, $response->getStatusCode());
        }
    }

    /** @test */
    public function it_respects_wildcard_patterns_in_excluded_paths()
    {
        // Enable rate limiter with low limits
        Config::set('rate-limiter.enabled', true);
        Config::set('rate-limiter.max_attempts', 1);
        Config::set('rate-limiter.decay_minutes', 1);
        
        // Exclude presisi paths with wildcard
        Config::set('rate-limiter.exclude_paths', ['presisi/*']);

        // Test that excluded paths work
        $response1 = $this->get('/presisi');
        $this->assertNotEquals(429, $response1->getStatusCode());

        $response2 = $this->get('/presisi/kesehatan');
        $this->assertNotEquals(429, $response2->getStatusCode());

        $response3 = $this->get('/presisi/bantuan');
        $this->assertNotEquals(429, $response3->getStatusCode());
    }

    /** @test */
    public function it_respects_different_rate_limits_for_different_ips()
    {
        // Enable rate limiter with low limits
        Config::set('rate-limiter.enabled', true);
        Config::set('rate-limiter.max_attempts', 2);
        Config::set('rate-limiter.decay_minutes', 1);

        // Simulate requests from different IPs
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.1'])->get('/');
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.1'])->get('/');
        
        // Third request from same IP should be blocked
        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.1'])->get('/');
        $this->assertEquals(429, $response->getStatusCode());

        // But requests from different IP should still work
        $response = $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.2'])->get('/');
        $this->assertNotEquals(429, $response->getStatusCode());
    }

    /** @test */
    public function it_works_with_simple_paths()
    {
        // Enable rate limiter with low limits
        Config::set('rate-limiter.enabled', true);
        Config::set('rate-limiter.max_attempts', 1);
        Config::set('rate-limiter.decay_minutes', 1);
        
        // Exclude a simple path
        Config::set('rate-limiter.exclude_paths', ['sitemap.xml']);

        // Make multiple requests to excluded path
        for ($i = 0; $i < 3; $i++) {
            $response = $this->get('/sitemap.xml');
            
            $this->assertNotEquals(429, $response->getStatusCode());
        }
    }
}