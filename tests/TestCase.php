<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    public function actingAsAdmin($admin) {
        $defaultGuard = config('auth.defaults.guard');
        $this->actingAs($admin, 'web');
        \Auth::shouldUse($defaultGuard);

        return $this;
    }
}
