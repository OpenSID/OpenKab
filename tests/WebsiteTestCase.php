<?php

namespace Tests;

use App\Http\Transformers\IdentitasTransformer;
use App\Http\Transformers\SettingTransformer;
use App\Models\Identitas;
use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\View;

class WebsiteTestCase extends TestCase
{
    use CreatesApplication, DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
        $this->enableWebsite();
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

    private function enableWebsite()
    {
        Setting::updateOrCreate(
            ['key' => 'website_enable'],
            [
                'name' => 'Pengaturan aktivasi website',
                'value' => 1,
                'type' => 'dropdown',
                'attribute' => [
                    ['text' => 'Tidak Aktif', 'value' => 0],
                    ['text' => 'Aktif', 'value' => 1],
                ],
                'description' => 'Pengaturan apakah website aktif atau tidak',
            ]
        );
        Setting::updateOrCreate(
            ['key' => 'home_page'],
            [
                'name' => 'Pengaturan halaman utama website',
                'value' => 'default',
                'type' => 'dropdown',
                'attribute' => [
                    ['text' => 'Default', 'value' => 'default'],
                    ['text' => 'Presisi', 'value' => 'presisi'],
                ],
                'description' => 'Pengaturan halaman utama website',
            ]
        );
    }
}
