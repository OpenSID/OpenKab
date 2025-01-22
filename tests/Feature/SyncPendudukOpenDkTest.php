<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Config;
use App\Models\Penduduk;
use App\Models\Setting;
use Illuminate\Http\Response;
use Tests\TestCase;

class SyncPendudukOpenDkTest extends TestCase
{

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_data_penduduk_by_kode_kecamatan()
    {
        $token = Setting::where('key', 'opendk_api_key')->first()->value;
        $kodeKecamatan = Config::inRandomOrder()->first()->kode_kecamatan;

        $totalKecamatan = Penduduk::whereRelation('config','kode_kecamatan', $kodeKecamatan)->count();
        if (! $token) {
            $this->fail('Token not found');
        }

        $url = '/api/v1/sync-penduduk-opendk?'.http_build_query([
            'filter[kode_kecamatan]' => $kodeKecamatan,
        ]);

        // Kirim permintaan sync penduduk dengan header Authorization
        $response = $this->getJson($url, [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.$token,
        ]); 

        // Pastikan responsnya berhasil
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonPath('meta.pagination.total', $totalKecamatan);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'attributes' => [                        
                        'config_id',
                        'nama',
                        'nik',                        
                    ],
                ],
            ],
        ]);
    }
}
