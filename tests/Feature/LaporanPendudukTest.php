<?php

namespace Tests\Feature;

use App\Models\Config;
use App\Models\LaporanSinkronisasi;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Http\Response;

class LaporanPendudukTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_laporan_penduduk_by_kode_kecamatan()
    {
        $token = Setting::where('key', 'opendk_api_key')->first()->value;
        $kodeKecamatan = Config::inRandomOrder()->first()->kode_kecamatan;

        $totalKecamatan = LaporanSinkronisasi::whereRelation('config','kode_kecamatan', $kodeKecamatan)->count();
        if (! $token) {
            $this->fail('Token not found');
        }

        $url = '/api/v1/opendk/laporan-penduduk?'.http_build_query([
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
                        'tipe',
                        'judul',                        
                        'tahun',                        
                        'bulan',                        
                        'nama_file',                        
                        'kirim',                        
                    ],
                ],
            ],
        ]);
    }
}
