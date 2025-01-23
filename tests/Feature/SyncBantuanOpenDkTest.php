<?php

namespace Tests\Feature;

use App\Models\Bantuan;
use App\Models\Config;
use App\Models\Setting;
use Illuminate\Http\Response;
use Tests\TestCase;

class SyncBantuanOpenDkTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_data_bantuan_by_kode_kecamatan()
    {
        $token = Setting::where('key', 'opendk_api_key')->first()->value;
        $kodeKecamatan = Config::inRandomOrder()->first()->kode_kecamatan;

        $totalKecamatan = Bantuan::whereRelation('config', 'kode_kecamatan', $kodeKecamatan)->count();

        $url = '/api/v1/opendk/bantuan?'.http_build_query([
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
                        'nama',
                        'asaldana',
                    ],
                ],
            ],
        ]);
    }
}
