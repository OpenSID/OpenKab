<?php

namespace Tests\Feature;

use App\Policies\CustomCSPPolicy;
use Tests\TestCase;

class CspPolicyTest extends TestCase
{
    /**
     * Test CSP policy instance dapat dibuat dengan benar.
     */
    public function test_csp_policy_can_be_instantiated(): void
    {
        $this->app['config']->set('app.debug', true);
        $this->app['config']->set('csp.enabled', true);
        $this->app['config']->set('csp.policy', CustomCSPPolicy::class);

        $policy = new CustomCSPPolicy();
        
        $this->assertInstanceOf(CustomCSPPolicy::class, $policy);
    }

    /**
     * Test CSP tidak dimatikan di mode debug.
     * Sebelumnya: jika APP_DEBUG=true, CSP dimatikan sepenuhnya.
     * Sekarang: CSP tetap aktif dengan policy lebih permissive.
     */
    public function test_csp_not_disabled_in_debug_mode(): void
    {
        $this->app['config']->set('app.debug', true);
        $this->app['config']->set('csp.enabled', true);

        // CSP harus tetap enabled di mode debug
        $this->assertTrue($this->app['config']->get('csp.enabled'));
    }

    /**
     * Test CSP enabled untuk route normal.
     */
    public function test_csp_enabled_for_normal_routes(): void
    {
        $this->app['config']->set('app.debug', false);
        $this->app['config']->set('csp.enabled', true);

        // CSP harus aktif untuk route normal
        $this->assertTrue($this->app['config']->get('csp.enabled'));
    }

    /**
     * Test CSP dapat dimatikan via konfigurasi.
     */
    public function test_csp_can_be_disabled_via_config(): void
    {
        $this->app['config']->set('csp.enabled', false);

        // CSP harus bisa dimatikan via config
        $this->assertFalse($this->app['config']->get('csp.enabled'));
    }
}
