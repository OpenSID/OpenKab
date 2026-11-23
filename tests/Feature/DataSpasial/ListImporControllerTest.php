<?php

declare(strict_types=1);

namespace Tests\Feature\DataSpasial;

use App\Models\Setting;
use App\Models\User;
use App\Services\CakupanPenggunaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ListImporControllerTest extends TestCase
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

    private function fixture(string $nama): array
    {
        return json_decode((string) file_get_contents(__DIR__.'/Fixtures/'.$nama), true, 512, JSON_THROW_ON_ERROR);
    }

    private function fakeApi(): void
    {
        Http::fake([
            '*/dataspasial/sektor*' => Http::response(['data' => [[
                'type' => 'daftar sektor', 'id' => '6758',
                'attributes' => ['kode' => 'kesehatan', 'nama' => 'kesehatan', 'status' => 'aktif'],
            ]], 'meta' => ['pagination' => ['total' => 1, 'count' => 1, 'per_page' => 30, 'current_page' => 1, 'total_pages' => 1]]]),
            '*/dataspasial/nilai*' => Http::response($this->fixture('nilai-impor.json')),
            '*/dataspasial/fasilitas*' => Http::response($this->fixture('fasilitas.json')),
        ]);
    }

    private function actingAsOperatorImpor(): User
    {
        $user = makeRole('operator impor', ['dataspasial-impor-read']);
        // Hak akses wajib (tertutup secara default): kabupaten 5102 (memuat kedua kecamatan fixture) + sektor kesehatan.
        app(CakupanPenggunaService::class)->simpanSektor($user->id, ['kesehatan']);
        $this->actingAs($user);

        return $user;
    }

    // ---- US1: halaman ----

    public function test_halaman_daftar_hasil_impor_tampil_untuk_pengguna_berizin(): void
    {
        $this->fakeApi();
        $this->actingAsOperatorImpor();

        $this->get(route('dataspasial.impor.daftar'))
            ->assertStatus(200)
            ->assertViewIs('dataspasial.impor-list.index');
    }

    // ---- US3: akses ----

    public function test_pengguna_tanpa_izin_impor_mendapat_403_pada_halaman_dan_data(): void
    {
        $this->fakeApi();
        $this->actingAs(makeRole('tanpa izin impor', ['dataspasial-peta-read']));

        $this->get(route('dataspasial.impor.daftar'))->assertStatus(403);
        $this->getJson(route('dataspasial.impor.daftar.data', ['sektor_id' => 6758]))->assertStatus(403);
    }

    // ---- US1: data JSON ----

    public function test_data_json_hanya_memuat_baris_impor_dengan_paginasi(): void
    {
        $this->fakeApi();
        $this->actingAsOperatorImpor();

        $respons = $this->getJson(route('dataspasial.impor.daftar.data', [
            'sektor_id' => 6758,
            'tahun' => 2026,
            'page' => 1,
        ]));

        $respons->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['kode_desa', 'nama_desa', 'isian_kode', 'isian_label', 'nilai', 'periode', 'waktu_input', 'sumber_data']],
                'meta' => ['pagination' => ['total', 'per_page', 'current_page', 'total_pages']],
                'ringkasan' => ['total_baris', 'total_desa', 'total_nilai_angka'],
            ]);

        $this->assertSame(['impor'], array_values(array_unique($respons->json('data.*.sumber_data'))));
        $this->assertSame(25, $respons->json('meta.pagination.per_page'));
    }

    public function test_data_json_butuh_sektor(): void
    {
        $this->fakeApi();
        $this->actingAsOperatorImpor();

        $this->getJson(route('dataspasial.impor.daftar.data', []))->assertStatus(422);
    }

    public function test_tahun_harus_empat_digit_dalam_rentang(): void
    {
        $this->fakeApi();
        $this->actingAsOperatorImpor();

        $this->getJson(route('dataspasial.impor.daftar.data', ['sektor_id' => 6758, 'tahun' => '26']))
            ->assertStatus(422);
    }

    // ---- US2: filter ----

    public function test_kombinasi_filter_tanpa_hasil_menghasilkan_nol_bukan_galat(): void
    {
        $this->fakeApi();
        $this->actingAsOperatorImpor();

        $respons = $this->getJson(route('dataspasial.impor.daftar.data', [
            'sektor_id' => 6758,
            'isian_kode' => ['tidak_ada'],
        ]));

        $respons->assertStatus(200)
            ->assertJsonPath('data', [])
            ->assertJsonPath('meta.pagination.total', 0)
            ->assertJsonPath('meta.empty', true)
            ->assertJsonPath('ringkasan.total_baris', 0);
    }

    public function test_filter_isian_dan_desa_dikembalikan_di_json(): void
    {
        $this->fakeApi();
        $this->actingAsOperatorImpor();

        $respons = $this->getJson(route('dataspasial.impor.daftar.data', [
            'sektor_id' => 6758,
            'isian_kode' => ['bidan'],
            'kode_desa' => '5102102003',
        ]));

        $respons->assertStatus(200);
        $this->assertSame(['bidan'], array_values(array_unique($respons->json('data.*.isian_kode'))));
        $this->assertSame(['5102102003'], array_values(array_unique($respons->json('data.*.kode_desa'))));
    }

    // ---- Galat API: pesan generik ----

    public function test_api_tidak_tersedia_menampilkan_pesan_generik(): void
    {
        Http::fake([
            '*/dataspasial/nilai*' => Http::response(['message' => 'internal detail'], 500),
            '*/dataspasial/fasilitas*' => Http::response($this->fixture('fasilitas.json')),
            '*/dataspasial/sektor*' => Http::response(['data' => [[
                'type' => 'daftar sektor', 'id' => '6758',
                'attributes' => ['kode' => 'kesehatan', 'nama' => 'kesehatan', 'status' => 'aktif'],
            ]]]),
        ]);
        $this->actingAsOperatorImpor();

        $this->getJson(route('dataspasial.impor.daftar.data', ['sektor_id' => 6758]))
            ->assertStatus(200)
            ->assertJsonPath('meta.unavailable', true)
            ->assertJsonPath('meta.message', 'Data sementara tidak tersedia.')
            ->assertDontSee('internal detail');
    }
}
