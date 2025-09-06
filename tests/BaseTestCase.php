<?php

namespace Tests;

use App\Http\Middleware\VerifyCsrfToken;
use App\Http\Transformers\IdentitasTransformer;
use App\Http\Transformers\SettingTransformer;
use App\Models\Identitas;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\View;

class BaseTestCase extends TestCase
{
    use CreatesApplication, DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        // get a random user to act as the admin role
        $user = User::first();
        $this->actingAsAdmin($user)->withoutMiddleware(VerifyCsrfToken::class);
        // set share view data
        $identitasAplikasi = fractal(
            Identitas::first(),
            IdentitasTransformer::class,
            \League\Fractal\Serializer\JsonApiSerializer::class
        )->toArray()['data']['attributes'];

        $settingAplikasi = collect(
            fractal(
                Setting::all(),
                SettingTransformer::class,
                \League\Fractal\Serializer\JsonApiSerializer::class
            )->toArray()['data']
        )->pluck('attributes.value', 'attributes.key');

        // daftarkan data identitas aplikasi disini, karena akan dipakai di hampir semua view

        View::share('identitasAplikasi', $identitasAplikasi);
        View::share('settingAplikasi', $settingAplikasi);
    }

    public function actingAsAdmin($admin)
    {
        $defaultGuard = config('auth.defaults.guard');
        $this->actingAs($admin, 'web');
        \Auth::shouldUse($defaultGuard);

        return $this;
    }
}
