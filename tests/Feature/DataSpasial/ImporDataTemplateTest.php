<?php

namespace Tests\Feature\DataSpasial;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ImporDataTemplateTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware();
    }

    public function test_template_requires_sektor(): void
    {
        $response = $this->get(route('dataspasial.impor.template'));
        $this->assertTrue(in_array($response->status(), [302, 422]));
    }

    public function test_template_download_xlsx_with_desa_list_and_dropdown(): void
    {
        Http::fake([
            '*/api/v1/dataspasial/skema*' => Http::response([
                'data' => [[
                    'type' => 'skema',
                    'attributes' => [
                        'sektor' => 'kesehatan',
                        'isian' => [
                            ['kode' => 'jumlah_penduduk', 'nama' => 'Jumlah Penduduk'],
                        ],
                    ],
                ]],
            ]),
            '*/api/v1/config/desa*' => Http::response([
                'data' => [
                    [
                        'id' => '1',
                        'type' => 'desa',
                        'attributes' => [
                            'kode_desa' => '3501010001',
                            'nama_desa' => 'Desa Satu',
                        ],
                    ],
                    [
                        'id' => '2',
                        'type' => 'desa',
                        'attributes' => [
                            'kode_desa' => '3501010002',
                            'nama_desa' => 'Desa Dua',
                        ],
                    ],
                ],
            ]),
            '*/api/v1/config/kecamatan*' => Http::response(['data' => []]),
        ]);

        $response = $this->get(route('dataspasial.impor.template', [
            'sektor' => 'kesehatan',
            'kecamatan' => '350101',
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
    }
}
