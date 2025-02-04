<?php

namespace Tests\Feature;

use App\Models\Config;
use App\Models\Keuangan;
use App\Models\Setting;
use Illuminate\Http\Response;
use Tests\TestCase;

class ApbdesTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_get_laporan_apbdes_by_kode_kecamatan()
    {
        $token = Setting::where('key', 'opendk_api_key')->first()->value;
        $kodeKecamatan = Config::inRandomOrder()->first()->kode_kecamatan;

        $total = Keuangan::whereRelation('desa', 'kode_kecamatan', $kodeKecamatan)->count();
        if (! $token) {
            $this->fail('Token not found');
        }

        $url = '/api/v1/keuangan/laporan_apbdes?'.http_build_query([
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
        $response->assertJsonPath('meta.pagination.total', $total);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'attributes' => [
                        'template_uuid',
                        'tahun',
                        'anggaran',
                        'realisasi',
                        'nama_desa',
                        'uraian',
                    ],
                ],
            ],
        ]);
    }
}
