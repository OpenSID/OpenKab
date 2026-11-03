<?php

namespace Tests\Feature\Peta;

use App\Exports\LokasiTemplateExport;
use App\Imports\LokasiImport;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use PHPUnit\Framework\Attributes\Test;
use Tests\BaseTestCase;
use ZipArchive;

class LokasiImportTest extends BaseTestCase
{
    private const KODE_DESA_A = '50.01.01.2001';

    private const KODE_DESA_B = '50.01.02.2001';

    public function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        config(['app.databaseGabunganUrl' => 'https://api.example.com']);

        session()->put('desa', [
            'kode_desa' => self::KODE_DESA_A,
            'nama_desa' => 'Desa Contoh',
        ]);
    }

    /**
     * Pasang fake API sekali per test memakai satu closure agar urutan stub tidak ambigu.
     *
     * @param  array<int, array<string, mixed>>  $existingPlans
     * @param  array<int, array<string, mixed>>  $desaAttributes
     */
    private function fakeApi(array $existingPlans = [], array $desaAttributes = [], bool $storeSucceeds = true): void
    {
        $desaAttributes = $desaAttributes ?: [[
            'kode_desa' => self::KODE_DESA_A,
            'nama_desa' => 'Desa Contoh',
            'kode_kecamatan' => '50.01.01',
            'nama_kecamatan' => 'Contoh',
        ]];

        Http::fake(function ($request) use ($existingPlans, $desaAttributes, $storeSucceeds) {
            $url = $request->url();
            $method = strtoupper($request->method());

            if (str_contains($url, '/api/v1/config/desa')) {
                return Http::response([
                    'data' => collect($desaAttributes)->values()->map(fn ($attrs, $i) => [
                        'id' => (string) ($i + 1),
                        'attributes' => $attrs,
                    ])->all(),
                ], 200);
            }

            if (str_contains($url, '/api/v1/point')) {
                return Http::response([
                    'data' => [
                        ['id' => '1', 'nama' => 'Gedung', 'children' => [['id' => '10', 'nama' => 'Pemerintahan']]],
                    ],
                ], 200);
            }

            if (str_contains($url, '/api/v1/plan/import')) {
                $rows = (array) ($request->data()['data'] ?? []);

                return Http::response([
                    'success' => true,
                    'summary' => ['success' => count($rows), 'failed' => 0],
                ], 200);
            }

            if (str_contains($url, '/api/v1/plan/store')) {
                return Http::response($storeSucceeds ? ['data' => ['id' => '99']] : ['message' => 'gagal'], $storeSucceeds ? 200 : 500);
            }

            if (str_contains($url, '/api/v1/plan/hapus')) {
                return Http::response(['success' => true], 200);
            }

            if ($method === 'DELETE' && str_contains($url, '/api/v1/plan')) {
                return Http::response(['success' => true], 200);
            }

            if (str_contains($url, '/api/v1/plan')) {
                return Http::response(['data' => $existingPlans], 200);
            }

            return Http::response([], 200);
        });
    }

    private function upload(string $path): UploadedFile
    {
        return new UploadedFile(
            $path,
            'template.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true
        );
    }

    #[Test]
    public function template_export_contains_desa_reference_sheet_and_per_row_header(): void
    {
        $path = storage_path('app/templates/testing_export.xlsx');
        @mkdir(dirname($path), 0775, true);

        (new LokasiTemplateExport(
            [['kode_desa' => self::KODE_DESA_A, 'nama_desa' => 'Desa Contoh']],
            'Gedung',
            'Pemerintahan',
        ))->write($path);

        $this->assertFileExists($path);

        $zip = new ZipArchive;
        $this->assertTrue($zip->open($path) === true);
        $workbook = (string) $zip->getFromName('xl/workbook.xml');
        $sheet1 = (string) $zip->getFromName('xl/worksheets/sheet1.xml');

        $this->assertStringContainsString('Daftar Desa', $workbook);
        $this->assertStringContainsString('dataValidations', $sheet1);
        $this->assertStringContainsString('Nama Desa', $sheet1);
        $this->assertStringContainsString('Nama Lokasi', $sheet1);
        $this->assertStringContainsString('sqref="A7:A1006"', $sheet1);
        $this->assertStringContainsString('$B$2:$B$2', $sheet1);
        $this->assertStringContainsString('Desa Contoh', (string) $zip->getFromName('xl/worksheets/sheet2.xml'));
        $zip->close();

        $import = new LokasiImport;
        $result = $import->process($path);

        $this->assertSame('Gedung', $result['meta']['jenis']);
        $this->assertSame('Pemerintahan', $result['meta']['kategori']);

        @unlink($path);
    }

    #[Test]
    public function import_rejects_rows_with_invalid_coordinates_and_flags_valid_rows(): void
    {
        $file = $this->makeXlsxFile([
            ['Nama Desa', 'Nama Lokasi', 'Latitude', 'Longitude', 'Keterangan'],
            ['Desa Contoh', 'Kantor Desa', '-6.2', '106.8', 'pusat'],
            ['Desa Contoh', 'Balai Desa', 'abc', '106.9', ''],
            ['Desa Contoh', 'Posyandu', '-6.3', '200', ''],
        ]);

        $import = new LokasiImport;
        $result = $import->process($file);

        $this->assertCount(3, $result['rows']);
        $this->assertCount(1, $result['valid']);
        $this->assertCount(2, $result['errors']);

        @unlink($file);
    }

    #[Test]
    public function import_rejects_nama_longer_than_50_characters(): void
    {
        $file = $this->makeXlsxFile([
            ['Nama Desa', 'Nama Lokasi', 'Latitude', 'Longitude', 'Keterangan'],
            ['Desa Contoh', str_repeat('A', 51), '-6.2', '106.8', ''],
            ['Desa Contoh', 'Balai Desa', '-6.3', '106.7', ''],
        ]);

        $import = new LokasiImport;
        $result = $import->process($file);

        $this->assertCount(2, $result['rows']);
        $this->assertCount(1, $result['valid']);
        $this->assertCount(1, $result['errors']);

        @unlink($file);
    }

    #[Test]
    public function import_rejects_row_with_empty_desa(): void
    {
        $file = $this->makeXlsxFile([
            ['Nama Desa', 'Nama Lokasi', 'Latitude', 'Longitude', 'Keterangan'],
            ['', 'Kantor Desa', '-6.2', '106.8', ''],
        ]);

        $import = new LokasiImport;
        $result = $import->process($file);

        $this->assertCount(1, $result['rows']);
        $this->assertCount(0, $result['valid']);
        $this->assertCount(1, $result['errors']);

        @unlink($file);
    }

    #[Test]
    public function import_rejects_coordinates_outside_indonesia(): void
    {
        $file = $this->makeXlsxFile([
            ['Nama Desa', 'Nama Lokasi', 'Latitude', 'Longitude', 'Keterangan'],
            ['Desa Contoh', 'Luar', '10', '100', ''],
            ['Desa Contoh', 'Dalam', '-6.2', '106.8', ''],
        ]);

        $import = new LokasiImport;
        $result = $import->process($file);

        $this->assertCount(2, $result['rows']);
        $this->assertCount(1, $result['valid']);
        $this->assertCount(1, $result['errors']);

        @unlink($file);
    }

    #[Test]
    public function import_rejects_unknown_desa_name(): void
    {
        $this->fakeApi();

        $file = $this->makeXlsxFile([
            ['Nama Desa', 'Nama Lokasi', 'Latitude', 'Longitude', 'Keterangan'],
            ['Desa Tidak Ada', 'Kantor Desa', '-6.2', '106.8', ''],
        ]);

        $response = $this->post(route('plan.import'), [
            'jenis' => '1',
            'kategori' => '10',
            'file' => $this->upload($file),
        ]);

        $response->assertRedirect(route('plan'));
        $response->assertSessionHas('import_summary', function (array $summary) {
            return $summary['success'] === 0 && $summary['failed'] === 1;
        });

        @unlink($file);
    }

    #[Test]
    public function import_rejects_ambiguous_desa_name(): void
    {
        $this->fakeApi([], [
            ['kode_desa' => '50.01.01.2001', 'nama_desa' => 'Desa Sama'],
            ['kode_desa' => '50.01.02.2001', 'nama_desa' => 'Desa Sama'],
        ]);

        $file = $this->makeXlsxFile([
            ['Nama Desa', 'Nama Lokasi', 'Latitude', 'Longitude', 'Keterangan'],
            ['Desa Sama', 'Kantor Desa', '-6.2', '106.8', ''],
        ]);

        $response = $this->post(route('plan.import'), [
            'jenis' => '1',
            'kategori' => '10',
            'file' => $this->upload($file),
        ]);

        $response->assertRedirect(route('plan'));
        $response->assertSessionHas('import_summary', function (array $summary) {
            return $summary['success'] === 0 && $summary['failed'] === 1;
        });

        @unlink($file);
    }

    #[Test]
    public function import_stores_valid_multi_desa_rows_via_batch_api(): void
    {
        $this->fakeApi([], [
            ['kode_desa' => self::KODE_DESA_A, 'nama_desa' => 'Desa A'],
            ['kode_desa' => self::KODE_DESA_B, 'nama_desa' => 'Desa B'],
        ]);

        $file = $this->makeXlsxFile([
            ['Nama Desa', 'Nama Lokasi', 'Latitude', 'Longitude', 'Keterangan'],
            ['Desa A', 'Kantor Desa', '-6.2', '106.8', 'pusat'],
            ['Desa B', 'Posyandu', '-6.3', '106.7', ''],
        ]);

        $response = $this->post(route('plan.import'), [
            'jenis' => '1',
            'kategori' => '10',
            'file' => $this->upload($file),
        ]);

        $response->assertRedirect(route('plan'));
        $response->assertSessionHas('import_summary', function (array $summary) {
            return $summary['total'] === 2 && $summary['success'] === 2 && $summary['failed'] === 0;
        });

        Http::assertSent(function ($request) {
            if (! str_contains($request->url(), '/api/v1/plan/import')) {
                return false;
            }

            $data = (array) ($request->data()['data'] ?? []);

            return count($data) === 2
                && $data[0]['config_id'] === 1
                && $data[0]['ref_point'] === 10
                && $data[0]['nama'] === 'Kantor Desa'
                && $data[0]['desk'] === 'pusat'
                && $data[0]['lat'] === '-6.2'
                && $data[0]['lng'] === '106.8'
                && $data[1]['config_id'] === 2
                && $data[1]['ref_point'] === 10
                && $data[1]['nama'] === 'Posyandu';
        });

        @unlink($file);
    }

    #[Test]
    public function import_is_insert_only_and_rejects_duplicate_names_per_desa(): void
    {
        $this->fakeApi([
            ['id' => '5', 'nama' => 'Kantor Desa'],
        ]);

        $file = $this->makeXlsxFile([
            ['Nama Desa', 'Nama Lokasi', 'Latitude', 'Longitude', 'Keterangan'],
            ['Desa Contoh', 'Kantor Desa', '-6.2', '106.8', ''],
            ['Desa Contoh', 'Posyandu', '-6.3', '106.7', ''],
        ]);

        $response = $this->post(route('plan.import'), [
            'jenis' => '1',
            'kategori' => '10',
            'file' => $this->upload($file),
        ]);

        $response->assertSessionHas('import_summary', function (array $summary) {
            return $summary['success'] === 1 && $summary['failed'] === 1;
        });

        @unlink($file);
    }

    #[Test]
    public function download_template_requires_jenis_and_kategori(): void
    {
        $this->fakeApi();

        $response = $this->get(route('plan.import.template'));

        $response->assertSessionHasErrors(['jenis', 'kategori']);
    }

    #[Test]
    public function download_template_returns_xlsx_without_desa_param(): void
    {
        $this->fakeApi();

        $response = $this->get(route('plan.import.template', [
            'jenis' => '1',
            'kategori' => '10',
        ]));

        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml', (string) $response->headers->get('content-type'));
    }

    #[Test]
    public function store_manual_lokasi_sends_payload_to_api(): void
    {
        $this->fakeApi();

        $response = $this->post(route('plan.store'), [
            'jenis' => '1',
            'kategori' => '10',
            'kode_desa' => self::KODE_DESA_A,
            'nama' => 'Kantor Baru',
            'lat' => '-6.1',
            'lng' => '106.9',
            'keterangan' => 'manual',
        ]);

        $response->assertRedirect(route('plan'));
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '/api/v1/plan/store')
                && $request['nama'] === 'Kantor Baru'
                && (int) $request['config_id'] === 1
                && (int) $request['ref_point'] === 10
                && $request['desk'] === 'manual'
                && $request['lat'] === '-6.1'
                && $request['lng'] === '106.9';
        });
    }

    #[Test]
    public function edit_detail_returns_plan_data_for_modal(): void
    {
        $this->fakeApi([
            [
                'id' => '5',
                'nama' => 'Kantor Lama',
                'desk' => 'lama',
                'lat' => '-6.2',
                'lng' => '106.8',
                'ref_point' => 10,
                'kode_desa' => self::KODE_DESA_A,
                'jenis' => 'Gedung',
                'kategori' => 'Pemerintahan',
            ],
        ]);

        $response = $this->getJson(route('plan.detail', ['id' => '5']));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.nama', 'Kantor Lama')
            ->assertJsonPath('data.id', '5');
    }

    #[Test]
    public function edit_detail_returns_not_found_for_missing_plan(): void
    {
        $this->fakeApi();

        $response = $this->getJson(route('plan.detail', ['id' => '99']));

        $response->assertNotFound()->assertJsonPath('success', false);
    }

    #[Test]
    public function update_lokasi_sends_payload_to_api(): void
    {
        $this->fakeApi([
            ['id' => '5', 'nama' => 'Kantor Lama', 'kode_desa' => self::KODE_DESA_A, 'ref_point' => '10'],
        ]);

        $response = $this->post(route('plan.update', ['id' => '5']), [
            '_method' => 'PUT',
            'jenis' => '1',
            'kategori' => '10',
            'kode_desa' => self::KODE_DESA_A,
            'nama' => 'Kantor Baru',
            'lat' => '-6.1',
            'lng' => '106.9',
            'keterangan' => 'diperbarui',
        ]);

        $response->assertRedirect(route('plan'));
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            return $request->method() === 'PUT'
                && str_contains($request->url(), '/api/v1/plan/update/5')
                && $request['nama'] === 'Kantor Baru'
                && (int) $request['config_id'] === 1
                && (int) $request['ref_point'] === 10
                && $request['desk'] === 'diperbarui'
                && $request['lat'] === '-6.1'
                && $request['lng'] === '106.9';
        });
    }

    #[Test]
    public function store_manual_lokasi_validates_coordinates(): void
    {
        $this->fakeApi();

        $response = $this->post(route('plan.store'), [
            'jenis' => '1',
            'kategori' => '10',
            'kode_desa' => self::KODE_DESA_A,
            'nama' => 'Kantor',
            'lat' => '200',
            'lng' => '106.9',
        ]);

        $response->assertSessionHasErrors('lat');
    }

    #[Test]
    public function store_manual_lokasi_rejects_coordinates_outside_indonesia(): void
    {
        $this->fakeApi();

        $response = $this->post(route('plan.store'), [
            'jenis' => '1',
            'kategori' => '10',
            'kode_desa' => self::KODE_DESA_A,
            'nama' => 'Kantor Luar',
            'lat' => '10.5',
            'lng' => '106.9',
        ]);

        $response->assertSessionHasErrors('lat');
    }

    #[Test]
    public function bulk_destroy_lokasi_sends_single_delete_with_ids_array(): void
    {
        $this->fakeApi();

        $response = $this->delete(route('plan.destroy.bulk'), [
            'ids' => ['1', '2'],
        ]);

        $response->assertRedirect(route('plan'));
        $response->assertSessionHas('success');

        Http::assertSent(function ($request) {
            if ($request->method() !== 'DELETE' || ! str_contains($request->url(), '/api/v1/plan/')) {
                return false;
            }

            parse_str((string) parse_url($request->url(), PHP_URL_QUERY), $query);

            return ($query['ids'] ?? null) === ['1', '2'];
        });
    }

    #[Test]
    public function bulk_destroy_lokasi_requires_ids(): void
    {
        $this->fakeApi();

        $response = $this->delete(route('plan.destroy.bulk'), []);

        $response->assertSessionHasErrors('ids');
    }

    #[Test]
    public function destroy_lokasi_calls_delete_api(): void
    {
        $this->fakeApi();

        $response = $this->delete(route('plan.destroy', ['id' => '5']));

        $response->assertRedirect(route('plan'));
        $response->assertSessionHas('success');

        Http::assertSent(fn ($request) => str_contains($request->url(), '/api/v1/plan/hapus/5'));
    }

    #[Test]
    public function plan_page_renders_import_filter_and_manual_controls(): void
    {
        $this->fakeApi();

        $response = $this->get(route('plan'));

        $response->assertOk();
        $response->assertSee('data-testid="bt-import"', false);
        $response->assertSee('data-testid="bt-tambah"', false);
        $response->assertSee('data-testid="import-jenis"', false);
        $response->assertSee('data-testid="import-kategori"', false);
        $response->assertSee('data-testid="bt-download-template"', false);
        $response->assertSee('data-testid="import-file"', false);
        $response->assertSee('data-testid="lokasi-kode-desa"', false);
        $response->assertSee('data-testid="filter-kabupaten"', false);
        $response->assertSee('data-testid="filter-kecamatan"', false);
        $response->assertSee('data-testid="filter-desa"', false);
        $response->assertSee('data-testid="bt-bulk-delete"', false);
    }

    /**
     * Buat file xlsx sementara: baris konteks + header per-baris + baris data.
     *
     * @param  array<int, array<int, string>>  $rows
     */
    private function makeXlsxFile(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'lokasi_').'.xlsx';

        $writer = new Writer;
        $writer->openToFile($path);

        $writer->addRow(Row::fromValues(['Jenis Lokasi', 'Gedung']));
        $writer->addRow(Row::fromValues(['Kategori Lokasi', 'Pemerintahan']));
        $writer->addRow(Row::fromValues(['']));

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();

        return $path;
    }
}
