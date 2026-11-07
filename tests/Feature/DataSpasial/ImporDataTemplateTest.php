<?php

namespace Tests\Feature\DataSpasial;

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use OpenSpout\Reader\XLSX\Reader;
use Tests\TestCase;

class ImporDataTemplateTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.databaseGabunganUrl' => 'https://api.example.test']);
        config(['dataspasial.cache_ttl' => 300]);

        Setting::query()->updateOrCreate(
            ['key' => 'database_gabungan_api_key'],
            ['value' => 'test-token']
        );

        Cache::flush();

        // Route dilindungi permission; withoutMiddleware tidak menghapus pengecekan FormRequest::authorize.
        $this->actingAs(makeRole('operator impor', ['dataspasial-impor-read']));
    }

    public function test_template_requires_sektor(): void
    {
        $response = $this->get(route('dataspasial.impor.template'));
        $this->assertTrue(in_array($response->status(), [302, 422]));
    }

    public function test_template_rejects_periode_bukan_tahun(): void
    {
        $response = $this->get(route('dataspasial.impor.template', [
            'sektor' => 'kesehatan',
            'periode' => '2026-01',
        ]));

        $response->assertSessionHasErrors('periode');
    }

    public function test_template_download_xlsx_with_desa_list_and_dropdown(): void
    {
        $this->fakeTemplateApi(fasilitas: [], nilai: []);

        $response = $this->get(route('dataspasial.impor.template', [
            'sektor' => 'kesehatan',
            'kecamatan' => '350101',
        ]));

        $response->assertStatus(200);
        $this->assertStringContainsString('.xlsx', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
    }

    public function test_template_prefills_existing_fasilitas_and_nilai(): void
    {
        $this->fakeTemplateApi(
            fasilitas: [
                [
                    'type' => 'ds_fasilitas',
                    'id' => '101',
                    'attributes' => [
                        'sektor' => ['id' => 6758, 'kode' => 'kesehatan'],
                        'kode_fasilitas' => 'PS-01',
                        'rujukan_fasilitas' => 'point:42',
                        'nama' => 'Posyandu Mawar',
                        'wilayah' => ['kode_desa' => '3501010001'],
                    ],
                ],
                // Fasilitas sektor lain tidak boleh ikut walaupun API mengembalikannya.
                [
                    'type' => 'ds_fasilitas',
                    'id' => '102',
                    'attributes' => [
                        'sektor' => ['id' => 6757, 'kode' => 'pendidikan'],
                        'kode_fasilitas' => 'SKL-01',
                        'nama' => 'SDN 1',
                        'wilayah' => ['kode_desa' => '3501010001'],
                    ],
                ],
            ],
            nilai: [
                // Nilai lama (bulanan) dan nilai terbaru untuk isian yang sama; yang terbaru dipakai.
                ['type' => 'daftar nilai DataSpasial', 'id' => '5000', 'attributes' => [
                    'fasilitas_id' => 101, 'isian_kode' => 'jumlah_penduduk', 'nilai' => 100,
                    'periode' => '2026-01', 'waktu_input' => '2026-01-10 08:00:00',
                ]],
                ['type' => 'daftar nilai DataSpasial', 'id' => '5001', 'attributes' => [
                    'fasilitas_id' => 101, 'isian_kode' => 'jumlah_penduduk', 'nilai' => 320,
                    'periode' => '2026-11', 'waktu_input' => '2026-11-03 09:06:34',
                ]],
                // Periode tahun lain tidak ikut.
                ['type' => 'daftar nilai DataSpasial', 'id' => '5002', 'attributes' => [
                    'fasilitas_id' => 101, 'isian_kode' => 'jumlah_penduduk', 'nilai' => 999,
                    'periode' => '2025-06', 'waktu_input' => '2025-06-01 00:00:00',
                ]],
            ],
        );

        $response = $this->get(route('dataspasial.impor.template', [
            'sektor' => 'kesehatan',
            'periode' => '2026',
        ]));

        $response->assertStatus(200);

        $rows = $this->readDataImporRows($response->getFile()->getPathname());

        $this->assertSame(['kode_desa', 'kode_fasilitas', 'nama_fasilitas', 'jumlah_penduduk'], $rows[0]);
        // Kode desa memakai format "kode - nama" seperti dropdown; hanya fasilitas kesehatan yang muncul.
        $this->assertSame(['3501010001 - Desa Satu', 'PS-01', 'Posyandu Mawar', 320], $rows[1]);
        $this->assertCount(2, $rows);

        // API menolak periode tahunan pada filter nilai, jadi permintaan nilai tidak boleh membawa periode.
        // Filter sektor pada fasilitas memakai sektor_id, bukan kode sektor.
        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/dataspasial/fasilitas')) {
                return false;
            }
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return ($query['sektor_id'] ?? null) === '6758';
        });

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/dataspasial/nilai')) {
                return false;
            }
            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return ! array_key_exists('periode', $query);
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $fasilitas
     * @param  array<int, array<string, mixed>>  $nilai
     */
    private function fakeTemplateApi(array $fasilitas, array $nilai): void
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
            '*/api/v1/dataspasial/sektor*' => Http::response([
                'data' => [
                    ['id' => '6758', 'type' => 'daftar sektor', 'attributes' => ['kode' => 'kesehatan']],
                    ['id' => '6757', 'type' => 'daftar sektor', 'attributes' => ['kode' => 'pendidikan']],
                ],
            ]),
            '*/api/v1/dataspasial/fasilitas*' => Http::response([
                'data' => $fasilitas,
                'meta' => ['pagination' => ['total_pages' => 1]],
            ]),
            '*/api/v1/dataspasial/nilai*' => Http::response([
                'data' => $nilai,
                'meta' => ['pagination' => ['total_pages' => 1]],
            ]),
            '*/api/v1/config/desa*' => Http::response([
                'data' => [
                    ['id' => '1', 'type' => 'desa', 'attributes' => ['kode_desa' => '3501010001', 'nama_desa' => 'Desa Satu']],
                    ['id' => '2', 'type' => 'desa', 'attributes' => ['kode_desa' => '3501010002', 'nama_desa' => 'Desa Dua']],
                ],
            ]),
            '*/api/v1/config/kecamatan*' => Http::response(['data' => []]),
        ]);
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    private function readDataImporRows(string $path): array
    {
        $reader = new Reader;
        $reader->open($path);
        $rows = [];
        foreach ($reader->getSheetIterator() as $sheet) {
            if ($sheet->getName() !== 'Data Impor') {
                continue;
            }
            foreach ($sheet->getRowIterator() as $row) {
                $rows[] = array_map(static fn ($cell) => $cell->getValue(), $row->getCells());
            }
        }
        $reader->close();

        $header = array_search('kode_desa', array_map(static fn ($r) => $r[0] ?? null, $rows), true);

        return $header === false ? [] : array_values(array_filter(
            array_slice($rows, $header),
            static fn ($r) => array_filter($r, static fn ($v) => $v !== null && $v !== '') !== []
        ));
    }
}
