<?php

declare(strict_types=1);

namespace Tests\Feature\DataSpasial;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class ImporDataTest extends TestCase
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

        fakeConfigEndpoint();
    }

    private function actingAsOperatorImpor(): User
    {
        $user = makeRole('operator impor', ['dataspasial-impor-read']);
        $this->actingAs($user);

        return $user;
    }

    public function test_menu_impor_bisa_diakses_oleh_user_dengan_permission(): void
    {
        $this->actingAsOperatorImpor();

        $this->get(route('dataspasial.impor'))->assertStatus(200);
    }

    public function test_template_membutuhkan_sektor_dan_wilayah(): void
    {
        $this->actingAsOperatorImpor();

        $this->postJson(route('dataspasial.impor.template'), [])->assertStatus(422);
    }

    public function test_upload_file_xlsx_diparse(): void
    {
        $this->actingAsOperatorImpor();

        $content = "kode_desa,kode_fasilitas,nama_fasilitas,jumlah_dokter\n5102012001,POS-001,Posyandu Mawar,4\n";
        $file = UploadedFile::fake()->createWithContent('faskes.xlsx', $content);

        $this->post(route('dataspasial.impor.upload'), ['file' => $file, 'sektor' => 'kesehatan'])
            ->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_tombol_simpan_tidak_aktif_sebelum_pratinjau(): void
    {
        $this->actingAsOperatorImpor();

        Session::put('impor_parsed', [
            'sektor' => 'kesehatan',
            'filename' => 'test.csv',
            'rows' => [],
        ]);
        Http::fake([
            '*/impor/pratinjau' => Http::response(['data' => [['impor_id' => 'imp001', 'diterima' => 0, 'ditolak' => 0]]], 200),
        ]);

        $this->post(route('dataspasial.impor.preview'))->assertStatus(422);
    }

    public function test_preview_mengembalikan_ringkasan_dan_baris_galat(): void
    {
        $this->actingAsOperatorImpor();

        Session::put('impor_parsed', [
            'sektor' => 'kesehatan',
            'filename' => 'template.xlsx',
            'rows' => [
                ['kode_desa' => '5102022004', 'kode_fasilitas' => 'FAS-01', 'nama_fasilitas' => 'Puskesmas', 'lokasi_id' => '42', 'jumlah_bidan' => '10'],
            ],
        ]);
        Http::fake([
            '*/impor/pratinjau' => Http::response([
                'data' => [[
                    'type' => 'pratinjau impor data spasial',
                    'id' => 'a7afd583-ad2a-404b-bd55-2e5094e0990b',
                    'attributes' => [
                        'impor_id' => 'a7afd583-ad2a-404b-bd55-2e5094e0990b',
                        'sektor' => ['kode' => 'kesehatan', 'nama' => 'Kesehatan'],
                        'filename' => 'template.xlsx',
                        'mode' => 'sebagian',
                        'ringkasan' => [
                            'total_baris' => 1,
                            'baris_siap' => 0,
                            'baris_menimpa' => 0,
                            'baris_konflik' => 0,
                            'baris_galat' => 1,
                            'bisa_disimpan' => false,
                        ],
                        'laporan' => [[
                            'nomor_baris' => 2,
                            'kode_desa' => '5102022004',
                            'nama_desa' => 'Mambang',
                            'kode_fasilitas' => 'FAS-01',
                            'nama_fasilitas' => 'Puskesmas',
                            'lokasi_id' => 42,
                            'periode' => '2026-10',
                            'status' => 'galat',
                            'pesan' => ['Kode isian tidak dikenal pada skema terbit.'],
                            'nilai' => ['jumlah_bidan' => '10'],
                        ]],
                    ],
                ]],
            ], 200),
            '*/api/v1/plan/get-list-coordinate*' => $this->lokasiResponse(),
        ]);

        $response = $this->post(route('dataspasial.impor.preview'));
        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('preview.impor_id', 'a7afd583-ad2a-404b-bd55-2e5094e0990b')
            ->assertJsonPath('preview.ringkasan.bisa_disimpan', false)
            ->assertJsonPath('preview.ringkasan.baris_galat', 1)
            ->assertJsonPath('preview.baris.0.nama_desa', 'Mambang')
            ->assertJsonPath('preview.baris.0.status', 'galat')
            ->assertJsonPath('preview.baris.0.nilai.jumlah_bidan', '10');
    }

    public function test_payload_pratinjau_membuang_key_internal(): void
    {
        $this->actingAsOperatorImpor();

        Session::put('impor_parsed', [
            'sektor' => 'kesehatan',
            'filename' => 'template.xlsx',
            'rows' => [
                ['kode_desa' => '5102022004', 'kode_fasilitas' => 'FAS-01', 'nama_fasilitas' => 'Puskesmas', 'lokasi_id' => '42 - Puskesmas (Mambang)', 'jumlah_bidan' => '10', '_kode_valid' => true],
            ],
        ]);

        Http::fake([
            '*/impor/pratinjau' => $this->previewResponse('imp1'),
            '*/api/v1/plan/get-list-coordinate*' => $this->lokasiResponse(),
        ]);

        $this->post(route('dataspasial.impor.preview'))->assertStatus(200);

        Http::assertSent(function ($request) {
            $body = $request->data();
            $nilai = $body['baris'][0]['nilai'] ?? [];

            return ! array_key_exists('_kode_valid', $nilai) && ! array_key_exists('lokasi_id', $nilai)
                && ($body['baris'][0]['lokasi_id'] ?? null) === 42 && ($nilai['jumlah_bidan'] ?? null) === '10';
        });
    }

    public function test_preview_simpan_diizinkan_saat_bisa_disimpan_true(): void
    {
        $this->actingAsOperatorImpor();

        Session::put('impor_parsed', [
            'sektor' => 'kesehatan',
            'filename' => 'template.xlsx',
            'rows' => [
                ['kode_desa' => '5102022004', 'kode_fasilitas' => 'FAS-01', 'nama_fasilitas' => 'Puskesmas', 'lokasi_id' => '42', 'jumlah_bidan' => '10'],
            ],
        ]);

        Http::fake([
            '*/impor/pratinjau' => $this->previewResponse('imp-ok'),
            '*/api/v1/plan/get-list-coordinate*' => $this->lokasiResponse(),
        ]);

        $this->post(route('dataspasial.impor.preview'))
            ->assertStatus(200)
            ->assertJsonPath('preview.ringkasan.bisa_disimpan', true)
            ->assertJsonPath('preview.impor_id', 'imp-ok');
    }

    public function test_paksa_timpa_tanpa_alasan_ditolak(): void
    {
        $this->actingAsOperatorImpor();
        Session::put('impor_parsed', ['sektor' => 'kesehatan', 'filename' => 'a.xlsx', 'rows' => []]);
        Http::fake();

        $this->postJson(route('dataspasial.impor.preview'), ['force_overwrite' => 1])
            ->assertStatus(422)
            ->assertJsonValidationErrors('alasan_override');

        Http::assertNothingSent();
    }

    public function test_paksa_timpa_meneruskan_alasan_ke_api(): void
    {
        $this->actingAsOperatorImpor();
        Session::put('impor_parsed', [
            'sektor' => 'kesehatan',
            'filename' => 'template.xlsx',
            'rows' => [
                ['kode_desa' => '5102022004', 'kode_fasilitas' => 'FAS-01', 'nama_fasilitas' => 'Puskesmas', 'lokasi_id' => '42', 'jumlah_bidan' => '10', '_kode_valid' => true],
            ],
        ]);

        Http::fake([
            '*/impor/pratinjau' => $this->previewResponse('imp-force'),
            '*/api/v1/plan/get-list-coordinate*' => $this->lokasiResponse(),
        ]);

        $this->post(route('dataspasial.impor.preview'), [
            'force_overwrite' => 1,
            'alasan_override' => 'Koreksi data dinas kesehatan',
        ])->assertStatus(200);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return ($body['force_overwrite'] ?? null) === true
                && ($body['alasan_override'] ?? null) === 'Koreksi data dinas kesehatan';
        });
    }

    public function test_tanpa_paksa_timpa_alasan_tidak_dikirim(): void
    {
        $this->actingAsOperatorImpor();
        Session::put('impor_parsed', [
            'sektor' => 'kesehatan',
            'filename' => 'template.xlsx',
            'rows' => [
                ['kode_desa' => '5102022004', 'kode_fasilitas' => 'FAS-01', 'nama_fasilitas' => 'Puskesmas', 'lokasi_id' => '42', 'jumlah_bidan' => '10', '_kode_valid' => true],
            ],
        ]);

        Http::fake([
            '*/impor/pratinjau' => $this->previewResponse('imp-no'),
            '*/api/v1/plan/get-list-coordinate*' => $this->lokasiResponse(),
        ]);

        $this->post(route('dataspasial.impor.preview'), [
            'force_overwrite' => 0,
            'alasan_override' => 'tidak relevan',
        ])->assertStatus(200);

        Http::assertSent(function ($request) {
            $body = $request->data();

            return ($body['force_overwrite'] ?? null) === false
                && ! array_key_exists('alasan_override', $body);
        });
    }

    public function test_index_hanya_menampilkan_sektor_dan_kecamatan(): void
    {
        $this->actingAsOperatorImpor();

        $this->get(route('dataspasial.impor'))->assertStatus(200)->assertViewHas('sektors')->assertViewHas('kecamatans');
    }

    public function test_download_template_memakai_form_request_validasi(): void
    {
        $this->actingAsOperatorImpor();

        $this->postJson(route('dataspasial.impor.template'), ['sektor' => ''])->assertStatus(422);
    }

    public function test_impor_menolak_lokasi_kosong_atau_lokasi_desa_lain(): void
    {
        $this->actingAsOperatorImpor();
        Http::fake(['*/api/v1/plan/get-list-coordinate*' => $this->lokasiResponse()]);

        foreach ([
            ['kode_desa' => '5102022004'],
            ['kode_desa' => '5102022005', 'lokasi_id' => '42'],
            ['kode_desa' => '5102022004', 'lokasi_id' => '999'],
        ] as $row) {
            Session::put('impor_parsed', ['sektor' => 'kesehatan', 'filename' => 'a.xlsx', 'rows' => [$row]]);
            $this->postJson(route('dataspasial.impor.preview'))->assertStatus(422);
        }
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/impor/pratinjau'));
    }

    public function test_impor_tidak_boleh_disimpan_jika_api_mengabaikan_lokasi(): void
    {
        $this->actingAsOperatorImpor();
        Session::put('impor_parsed', [
            'sektor' => 'kesehatan', 'filename' => 'a.xlsx',
            'rows' => [['kode_desa' => '5102022004', 'lokasi_id' => '42', 'kode_fasilitas' => 'F-1']],
        ]);
        Http::fake([
            '*/api/v1/plan/get-list-coordinate*' => $this->lokasiResponse(),
            '*/impor/pratinjau' => Http::response(['data' => [['attributes' => [
                'impor_id' => 'old-api', 'ringkasan' => ['bisa_disimpan' => true],
                'laporan' => [['nomor_baris' => 2, 'status' => 'siap']],
            ]]]]),
        ]);
        $this->postJson(route('dataspasial.impor.preview'))->assertStatus(503)
            ->assertJsonPath('success', false);
        $this->postJson(route('dataspasial.impor.simpan', ['impor_id' => 'old-api']))->assertStatus(422);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), '/simpan'));
    }

    public function test_impor_disimpan_setelah_api_mengonfirmasi_lokasi(): void
    {
        $this->actingAsOperatorImpor();
        Session::put('impor_parsed', [
            'sektor' => 'kesehatan', 'filename' => 'a.xlsx',
            'rows' => [['kode_desa' => '5102022004', 'lokasi_id' => '42', 'kode_fasilitas' => 'F-1']],
        ]);
        Http::fake([
            '*/api/v1/plan/get-list-coordinate*' => $this->lokasiResponse(),
            '*/impor/pratinjau' => $this->previewResponse('confirmed'),
            '*/impor/confirmed/simpan' => Http::response(['data' => [['id' => 'confirmed']]], 201),
        ]);

        $this->postJson(route('dataspasial.impor.preview'))->assertOk()
            ->assertJsonPath('preview.baris.0.lokasi_id', 42);
        $this->postJson(route('dataspasial.impor.simpan', ['impor_id' => 'another']))->assertStatus(422);
        $this->postJson(route('dataspasial.impor.simpan', ['impor_id' => 'confirmed']))->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_pratinjau_baru_yang_gagal_membatalkan_sesi_simpan_sebelumnya(): void
    {
        $this->actingAsOperatorImpor();
        Session::put('impor_preview', ['impor_id' => 'previous', 'ringkasan' => ['bisa_disimpan' => true]]);
        Session::put('impor_parsed', [
            'sektor' => 'kesehatan', 'filename' => 'a.xlsx',
            'rows' => [['kode_desa' => '5102022004', 'lokasi_id' => '42']],
        ]);
        Http::fake(['*/api/v1/plan/get-list-coordinate*' => Http::response(['message' => 'unavailable'], 503)]);

        $this->postJson(route('dataspasial.impor.preview'))->assertStatus(503)
            ->assertJsonPath('message', 'Daftar lokasi tidak tersedia. Silakan coba lagi.');
        $this->postJson(route('dataspasial.impor.simpan', ['impor_id' => 'previous']))->assertStatus(422);
    }

    private function lokasiResponse(): \GuzzleHttp\Promise\PromiseInterface
    {
        return Http::response([[
            'id' => 42, 'nama' => 'Puskesmas', 'lat' => -8, 'lng' => 115,
            'config' => ['kode_desa' => '5102022004', 'nama_desa' => 'Mambang'],
            'point' => ['nama' => 'Kesehatan'],
        ]]);
    }

    private function previewResponse(string $id): \GuzzleHttp\Promise\PromiseInterface
    {
        return Http::response(['data' => [['attributes' => [
            'impor_id' => $id,
            'ringkasan' => ['total_baris' => 1, 'baris_siap' => 1, 'baris_galat' => 0, 'bisa_disimpan' => true],
            'laporan' => [['nomor_baris' => 2, 'lokasi_id' => 42, 'status' => 'siap']],
        ]]]]);
    }
}
