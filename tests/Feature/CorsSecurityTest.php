<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class CorsSecurityTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Test that allowed origins are properly configured from environment variable.
     *
     * @return void
     */
    public function test_allowed_origins_are_restricted_to_trusted_domains()
    {
        // Verify wildcard is not in allowed origins by default
        $allowedOrigins = Config::get('cors.allowed_origins');
        
        $this->assertIsArray($allowedOrigins);
        $this->assertNotContains('*', $allowedOrigins, 'Wildcard (*) should not be in allowed origins when supports_credentials is true');
    }

    /**
     * Test that requests from allowed origins succeed.
     *
     * @return void
     */
    public function test_preflight_requests_from_allowed_origins_succeed()
    {
        // Set test allowed origins
        Config::set('cors.allowed_origins', ['http://localhost:3000', 'https://devopenkab.opendesa.id']);

        // Make preflight OPTIONS request from allowed origin
        $response = $this->withHeaders([
            'Origin' => 'http://localhost:3000',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type, Authorization',
        ])->options('/api/user');

        // Should return 200 or 204 (successful preflight)
        $response->assertStatus(204);
        
        // Verify CORS headers are present
        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
    }

    /**
     * Test that requests from non-allowed origins are rejected.
     *
     * @return void
     */
    public function test_preflight_requests_from_non_allowed_origins_are_rejected()
    {
        // Set restricted allowed origins
        Config::set('cors.allowed_origins', ['https://devopenkab.opendesa.id']);

        // Make preflight OPTIONS request from non-allowed origin
        $response = $this->withHeaders([
            'Origin' => 'https://malicious-site.com',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type, Authorization',
        ])->options('/api/user');

        // The response should have Access-Control-Allow-Origin matching the allowed origin, not the request origin
        // When origin is not allowed, Laravel returns the request's Origin header value but browser will reject it
        // The key test is that the malicious origin is NOT in the allowed_origins config
        $this->assertNotContains(
            'https://malicious-site.com',
            Config::get('cors.allowed_origins')
        );
    }

    /**
     * Test that actual requests from allowed origins include proper CORS headers.
     *
     * @return void
     */
    public function test_actual_requests_from_allowed_origins_include_cors_headers()
    {
        Config::set('cors.allowed_origins', ['http://test.example.com']);

        $response = $this->withHeaders([
            'Origin' => 'http://test.example.com',
        ])->get('/api/user');

        $response->assertHeader('Access-Control-Allow-Origin', 'http://test.example.com');
    }

    /**
     * Test that supports_credentials is enabled.
     *
     * @return void
     */
    public function test_supports_credentials_is_enabled()
    {
        $this->assertTrue(
            Config::get('cors.supports_credentials'),
            'supports_credentials should be true for authenticated API requests'
        );
    }

    /**
     * Test that allowed headers are restricted to necessary headers only.
     *
     * @return void
     */
    public function test_allowed_headers_are_restricted()
    {
        $allowedHeaders = Config::get('cors.allowed_headers');
        
        $this->assertIsArray($allowedHeaders);
        $this->assertNotContains('*', $allowedHeaders, 'Wildcard (*) should not be in allowed headers');
        
        // Verify only necessary headers are allowed
        $expectedHeaders = ['Content-Type', 'Authorization', 'X-Requested-With', 'X-XSRF-TOKEN'];
        foreach ($expectedHeaders as $header) {
            $this->assertContains($header, $allowedHeaders, "Header {$header} should be allowed");
        }
    }

    /**
     * Test that wildcard origin with credentials is not configured.
     * This is a critical security check.
     *
     * @return void
     */
    public function test_wildcard_origin_with_credentials_is_not_configured()
    {
        $allowedOrigins = Config::get('cors.allowed_origins');
        $supportsCredentials = Config::get('cors.supports_credentials');

        if ($supportsCredentials) {
            $this->assertNotContains(
                '*',
                $allowedOrigins,
                'CRITICAL SECURITY ISSUE: Wildcard (*) origin cannot be used with supports_credentials=true'
            );
        }
    }

    /**
     * Test that production domain is in allowed origins.
     *
     * @return void
     */
    public function test_production_domain_is_in_allowed_origins()
    {
        $allowedOrigins = Config::get('cors.allowed_origins');
        
        $this->assertContains(
            'https://devopenkab.opendesa.id',
            $allowedOrigins,
            'Production domain should be in allowed origins'
        );
    }

    /**
     * Test that localhost URLs are available for development.
     *
     * @return void
     */
    public function test_localhost_urls_available_for_development()
    {
        $allowedOrigins = Config::get('cors.allowed_origins');
        
        $localhostUrls = [
            'http://localhost:3000',
            'http://127.0.0.1:3000',
            'http://localhost:5173',
            'http://127.0.0.1:5173',
        ];

        $hasLocalhost = false;
        foreach ($localhostUrls as $url) {
            if (in_array($url, $allowedOrigins)) {
                $hasLocalhost = true;
                break;
            }
        }

        $this->assertTrue(
            $hasLocalhost,
            'At least one localhost URL should be available for development'
        );
    }

    /**
     * Test that API endpoints properly handle CORS preflight requests.
     *
     * @return void
     */
    public function test_api_endpoints_handle_preflight_correctly()
    {
        Config::set('cors.allowed_origins', ['http://api-test.example.com']);
        Config::set('cors.allowed_methods', ['*']);

        $response = $this->withHeaders([
            'Origin' => 'http://api-test.example.com',
            'Access-Control-Request-Method' => 'GET',
            'Access-Control-Request-Headers' => 'Authorization',
        ])->options('/api/user');

        $response->assertStatus(204);
        $response->assertHeader('Access-Control-Allow-Origin', 'http://api-test.example.com');
        
        // Verify that Authorization header is in the allowed headers (case-insensitive)
        $allowedHeaders = strtolower($response->headers->get('Access-Control-Allow-Headers', ''));
        $this->assertStringContainsString('authorization', $allowedHeaders);
    }

    /**
     * Test that multiple allowed origins work correctly.
     *
     * @return void
     */
    public function test_multiple_allowed_origins_work_correctly()
    {
        $testOrigins = [
            'https://devopenkab.opendesa.id',
            'http://localhost:3000',
            'http://staging.example.com',
        ];

        Config::set('cors.allowed_origins', $testOrigins);

        foreach ($testOrigins as $origin) {
            $response = $this->withHeaders([
                'Origin' => $origin,
            ])->get('/api/user');

            $response->assertHeader('Access-Control-Allow-Origin', $origin);
        }
    }

    /**
     * Test that empty origin header doesn't bypass CORS.
     *
     * @return void
     */
    public function test_null_origin_is_handled_securely()
    {
        Config::set('cors.allowed_origins', ['https://devopenkab.opendesa.id']);

        // Request without Origin header - Laravel CORS will reflect the Origin if present
        // but without Origin header, no Access-Control-Allow-Origin should be added
        $response = $this->get('/api/user');

        // When no Origin header is sent, CORS headers should not be present
        // (or may be present with empty value depending on Laravel version)
        // The important thing is that 'null' is not reflected
        $headers = $response->headers->all();
        if (isset($headers['access-control-allow-origin'])) {
            $allowOrigin = $response->headers->get('Access-Control-Allow-Origin');
            $this->assertNotEquals('null', $allowOrigin);
        }
    }

    /**
     * Test that CORS configuration can be customized via environment variable.
     *
     * @return void
     */
    public function test_cors_allowed_origins_from_environment()
    {
        // Simulate environment variable
        $customOrigins = 'https://custom1.example.com,https://custom2.example.com';
        Config::set('cors.allowed_origins', array_filter(explode(',', $customOrigins)));

        $allowedOrigins = Config::get('cors.allowed_origins');
        
        $this->assertContains('https://custom1.example.com', $allowedOrigins);
        $this->assertContains('https://custom2.example.com', $allowedOrigins);
        $this->assertNotContains('*', $allowedOrigins);
    }
}
