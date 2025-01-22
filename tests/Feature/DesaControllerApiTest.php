<?php

namespace Tests\Feature;

use App\Models\Config;
use App\Models\Setting;
use Illuminate\Http\Response;
use Tests\TestCase;

class DesaControllerApiTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_data_kecamatan_valid_token()
    {
        $token = Setting::where('key', 'opendk_api_key')->first()->value;
        $kecamatan = Config::inRandomOrder()->first()->kode_kecamatan;
        $totalDesa = Config::where('kode_kecamatan', $kecamatan)->count();
        if (! $token) {
            $this->fail('Token not found');
        }
        $url = '/api/v1/desa?'.http_build_query([
            'filter[kode_kecamatan]' => $kecamatan,
        ]);
        // Kirim permintaan delete_multiple dengan header Authorization
        $response = $this->getJson($url, [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ]);        
        // Pastikan responsnya berhasil
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonPath('meta.pagination.total', $totalDesa);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'attributes' => [                        
                        'kode_desa',
                        'nama_desa',
                        'sebutan_desa',                        
                    ],
                ],
            ],
        ]);
    }
}
