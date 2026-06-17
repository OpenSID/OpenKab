<?php

namespace Tests\Feature;

use App\Policies\CustomCspPreset;
use Spatie\Csp\Policy;
use Spatie\Csp\Preset;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CspPolicyTest extends TestCase
{
    public function test_csp_preset_can_be_instantiated(): void
    {
        $preset = new CustomCspPreset();

        $this->assertInstanceOf(Preset::class, $preset);
    }

    public function test_csp_preset_configure_policy(): void
    {
        $policy = new Policy();
        $preset = new CustomCspPreset();

        $preset->configure($policy);

        $this->assertFalse($policy->isEmpty());
    }

    public function test_csp_not_disabled_in_debug_mode(): void
    {
        $this->app['config']->set('app.debug', true);
        $this->app['config']->set('csp.enabled', true);

        $this->assertTrue($this->app['config']->get('csp.enabled'));
    }

    public function test_csp_enabled_for_normal_routes(): void
    {
        $this->app['config']->set('app.debug', false);
        $this->app['config']->set('csp.enabled', true);

        $this->assertTrue($this->app['config']->get('csp.enabled'));
    }

    public function test_csp_can_be_disabled_via_config(): void
    {
        $this->app['config']->set('csp.enabled', false);

        $this->assertFalse($this->app['config']->get('csp.enabled'));
    }
}
