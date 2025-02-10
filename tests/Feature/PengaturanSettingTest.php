<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\Response;
use Tests\TestCase;

class PengaturanSettingTest extends TestCase
{
    protected $setting;

    public function setUp(): void
    {
        parent::setUp();

        $user = User::inRandomOrder()->first();

        Sanctum::actingAs($user);

        $this->setting = Setting::inRandomOrder()->first();

    }

    /**
     * A basic feature test example.
     */
    public function test_get_data_setting(): void
    {
        $url = '/api/v1/pengaturan/settings';

        $response = $this->getJson($url); 

        // Pastikan responsnya berhasil
        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'attributes' => [                        
                        'key',
                        'name',
                        'value',                        
                        'type',   
                        'attribute' => [],
                        'description',
                        'created_at',
                        'updated_at'                     
                    ],
                ],
            ],
        ]);
    }

    public function test_update_data_setting_success(): void
    {
        $url = '/api/v1/pengaturan/settings/'.$this->setting->id;

        $data = [
            'home_page' => 'Default',
        ];

        $response = $this->putJson($url, $data);

        // Pastikan responsnya berhasil
        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonStructure([
            'success'
        ]);
    }

    public function test_update_data_setting_fail(): void
    {
        $url = '/api/v1/pengaturan/settings/'.$this->setting->id;

        $data = [
            'home_page_salah' => 'Default',
        ];

        $response = $this->putJson($url, $data);

        // Pastikan responsnya berhasil
        $response->assertStatus(Response::HTTP_BAD_REQUEST);

        $response->assertJsonStructure([
            'success',
            'message'
        ]);
    }
}