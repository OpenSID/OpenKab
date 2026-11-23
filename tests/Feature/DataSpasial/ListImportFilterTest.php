<?php

declare(strict_types=1);

namespace Tests\Feature\DataSpasial;

use App\Models\Setting;
use App\Services\CakupanPenggunaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Daftar Data Hasil Impor: filter tahun, filter versi skema (mengikuti sektor), dan dropdown sektor.
 */
class ListImportFilterTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.databaseGabunganUrl' => 'https://api.example.test']);
        Setting::query()->updateOrCreate(['key' => 'database_gabungan_api_key'], ['value' => 'test-token']);
        Cache::flush();
        fakeConfigEndpoint();

        $nilai = json_decode((string) file_get_contents(__DIR__.'/Fixtures/nilai-impor.json'), true);
        $nilai['data'][1]['attributes']['periode'] = '2025-03'; // satu baris tahun lain
        $nilai['data'][1]['attributes']['skema_versi'] = ['id' => 12909, 'nomor_versi' => 2, 'status' => 'terbit'];
        Http::fake([
            '*/dataspasial/nilai*' => Http::response($nilai),
            '*/dataspasial/fasilitas*' => Http::response(json_decode((string) file_get_contents(__DIR__.'/Fixtures/fasilitas.json'), true)),
            '*/dataspasial/skema/riwayat*' => Http::response(['data' => [
                ['type' => 'riwayat versi skema', 'id' => '12908', 'attributes' => ['nomor_versi' => 1, 'status' => 'terbit']],
                ['type' => 'riwayat versi skema', 'id' => '12909', 'attributes' => ['nomor_versi' => 2, 'status' => 'terbit']],
                ['type' => 'riwayat versi skema', 'id' => '12921', 'attributes' => ['nomor_versi' => 3, 'status' => 'draf']],
            ]]),
            '*/dataspasial/sektor*' => Http::response(['data' => [
                ['type' => 'daftar sektor', 'id' => '6758', 'attributes' => ['kode' => 'kesehatan', 'nama' => 'kesehatan']],
                ['type' => 'daftar sektor', 'id' => '6757', 'attributes' => ['kode' => 'pendidikan', 'nama' => 'Pendidikan']],
            ]]),
        ]);
    }

    public function test_filter_tahun_menyaring_dari_awalan_periode(): void
    {
        $this->actingAs(makeRole('operator impor', ['dataspasial-impor-read']));

        $this->getJson(route('dataspasial.impor.daftar.data', ['sektor_id' => 6758, 'tahun' => 2026]))
            ->assertStatus(200)
            ->assertJsonPath('meta.pagination.total', 2);

        $this->getJson(route('dataspasial.impor.daftar.data', ['sektor_id' => 6758, 'tahun' => 2025]))
            ->assertStatus(200)
            ->assertJsonPath('meta.pagination.total', 1);
    }

    public function test_filter_versi_menyaring_berdasarkan_nomor_versi_skema(): void
    {
        $this->actingAs(makeRole('operator impor', ['dataspasial-impor-read']));

        $this->getJson(route('dataspasial.impor.daftar.data', ['sektor_id' => 6758, 'versi' => 12908]))
            ->assertStatus(200)
            ->assertJsonPath('meta.pagination.total', 2);

        $this->getJson(route('dataspasial.impor.daftar.data', ['sektor_id' => 6758, 'versi' => 12909]))
            ->assertStatus(200)
            ->assertJsonPath('meta.pagination.total', 1);
    }

    public function test_endpoint_versi_mengembalikan_versi_sektor_terurut_menurun(): void
    {
        $this->actingAs(makeRole('operator impor', ['dataspasial-impor-read']));

        $respons = $this->getJson(route('dataspasial.impor.daftar.versi', ['sektor_id' => 6758]))
            ->assertStatus(200);

        $this->assertSame([3, 2, 1], $respons->json('data.*.nomor_versi'));
        Http::assertSent(fn ($request) => str_contains($request->url(), 'skema/riwayat') && str_contains($request->url(), 'sektor=kesehatan'));
    }

    public function test_endpoint_versi_dibatasi_hak_akses_sektor(): void
    {
        $user = makeRole('operator impor', ['dataspasial-impor-read']);
        app(CakupanPenggunaService::class)->simpanSektor($user->id, ['kesehatan']);
        $this->actingAs($user);

        $this->getJson(route('dataspasial.impor.daftar.versi', ['sektor_id' => 6757]))->assertStatus(403);
    }

    public function test_dropdown_versi_tersedia_dan_filter_tahun_tidak_ditampilkan(): void
    {
        $user = makeRole('operator impor', ['dataspasial-impor-read']);
        app(CakupanPenggunaService::class)->simpanSektor($user->id, ['kesehatan']);
        $this->actingAs($user);

        $this->get(route('dataspasial.impor.daftar'))
            ->assertStatus(200)
            ->assertDontSee('id="tahun"', false)
            ->assertSee('id="versi"', false)
            ->assertSee('data-kode="kesehatan"', false)
            ->assertDontSee('data-kode="pendidikan"', false);
    }

    public function test_peta_fasilitas_tidak_difilter_versi_agar_desa_tidak_hilang(): void
    {
        $this->actingAs(makeRole('operator impor', ['dataspasial-impor-read']));

        $this->getJson(route('dataspasial.impor.daftar.data', ['sektor_id' => 6758, 'versi' => 12909]))->assertStatus(200);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/fasilitas') && ! str_contains($request->url(), 'skema_versi_id'));
    }

    public function test_permintaan_nilai_membawa_skema_versi_id_dan_hasil_disaring_per_versi(): void
    {
        $this->actingAs(makeRole('operator impor', ['dataspasial-impor-read']));

        $this->getJson(route('dataspasial.impor.daftar.data', ['sektor_id' => 6758, 'versi' => 12909]))
            ->assertStatus(200)
            ->assertJsonPath('meta.pagination.total', 1);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/nilai') && str_contains($request->url(), 'skema_versi_id=12909'));
    }
}
