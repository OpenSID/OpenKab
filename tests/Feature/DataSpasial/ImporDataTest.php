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

        $this->post(route('dataspasial.impor.preview'))->assertStatus(200);
    }

    public function test_preview_mengembalikan_ringkasan_dan_baris_galat(): void
    {
        $this->actingAsOperatorImpor();

        Session::put('impor_parsed', [
            'sektor' => 'kesehatan',
            'filename' => 'template.xlsx',
            'rows' => [
                ['kode_desa' => '5102022004', 'kode_fasilitas' => 'FAS-01', 'nama_fasilitas' => 'Puskesmas', 'jumlah_bidan' => '10'],
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
                            'periode' => '2026-10',
                            'status' => 'galat',
                            'pesan' => ['Kode isian tidak dikenal pada skema terbit.'],
                            'nilai' => ['jumlah_bidan' => '10'],
                        ]],
                    ],
                ]],
            ], 200),
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
                ['kode_desa' => '5102022004', 'kode_fasilitas' => 'FAS-01', 'nama_fasilitas' => 'Puskesmas', 'jumlah_bidan' => '10', '_kode_valid' => true],
            ],
        ]);

        Http::fake([
            '*/impor/pratinjau' => Http::response(['data' => [['impor_id' => 'imp1', 'attributes' => ['ringkasan' => ['total_baris' => 1, 'baris_galat' => 0, 'bisa_disimpan' => true], 'laporan' => []]]]], 200),
        ]);

        $this->post(route('dataspasial.impor.preview'))->assertStatus(200);

        Http::assertSent(function ($request) {
            $body = $request->data();
            $nilai = $body['baris'][0]['nilai'] ?? [];

            return ! array_key_exists('_kode_valid', $nilai) && ($nilai['jumlah_bidan'] ?? null) === '10';
        });
    }

    public function test_preview_simpan_diizinkan_saat_bisa_disimpan_true(): void
    {
        $this->actingAsOperatorImpor();

        Session::put('impor_parsed', [
            'sektor' => 'kesehatan',
            'filename' => 'template.xlsx',
            'rows' => [
                ['kode_desa' => '5102022004', 'kode_fasilitas' => 'FAS-01', 'nama_fasilitas' => 'Puskesmas', 'jumlah_bidan' => '10'],
            ],
        ]);

        Http::fake([
            '*/impor/pratinjau' => Http::response(['data' => [['impor_id' => 'imp-ok', 'attributes' => ['ringkasan' => ['total_baris' => 1, 'baris_siap' => 1, 'baris_galat' => 0, 'bisa_disimpan' => true], 'laporan' => []]]]], 200),
        ]);

        $this->post(route('dataspasial.impor.preview'))
            ->assertStatus(200)
            ->assertJsonPath('preview.ringkasan.bisa_disimpan', true)
            ->assertJsonPath('preview.impor_id', 'imp-ok');
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
}
