<?php

namespace Tests\Feature;

use App\Models\Config;
use App\Models\Pembangunan;
use App\Models\Setting;
use Illuminate\Http\Response;
use Tests\TestCase;

class SyncPembangunanOpenDkTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_data_pembangunan_by_kode_kecamatan()
    {
        $token = Setting::where('key', 'opendk_api_key')->first()->value;
        $kodeKecamatan = Config::inRandomOrder()->first()->kode_kecamatan;

        $totalKecamatan = Pembangunan::whereRelation('config', 'kode_kecamatan', $kodeKecamatan)->count();

        $url = '/api/v1/opendk/pembangunan?'.http_build_query([
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
                        'judul',
                        'sumber_dana',
                    ],
                ],
            ],
        ]);
    }
}
