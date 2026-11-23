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
 * Hak akses wilayah dan sektor di seluruh fitur DataSpasial (issue OpenSID/OpenKab#1320).
 */
class AksesSemuaFiturTest extends TestCase
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

        Http::fake([
            '*/dataspasial/sektor*' => Http::response(['data' => [
                ['type' => 'daftar sektor', 'id' => '6758', 'attributes' => ['kode' => 'kesehatan', 'nama' => 'kesehatan', 'status' => 'aktif']],
                ['type' => 'daftar sektor', 'id' => '6757', 'attributes' => ['kode' => 'pendidikan', 'nama' => 'Pendidikan', 'status' => 'aktif']],
            ]]),
            '*/dataspasial/skema*' => Http::response(['data' => []]),
            '*/dataspasial/ringkasan*' => Http::response(['data' => []]),
        ]);
    }

    private function layanan(): CakupanPenggunaService
    {
        return app(CakupanPenggunaService::class);
    }

    public function test_filter_baris_membatasi_desa_di_luar_kecamatan_dan_menurunkan_kecamatan_dari_kode_desa(): void
    {
        $user = makeRole('analis opd', ['dataspasial-dasbor-read']);
        $this->layanan()->simpanWilayah($user->id, 'kecamatan', '510210');

        $filter = $this->layanan()->filterBaris($user->id, false);

        // Baris monitoring tidak memuat kode_kecamatan: diturunkan dari 6 digit pertama kode desa.
        $this->assertTrue($filter(['kode_desa' => '5102102003']));
        $this->assertFalse($filter(['kode_desa' => '5102022007']));
    }

    public function test_filter_baris_membatasi_sektor(): void
    {
        $user = makeRole('analis opd', ['dataspasial-dasbor-read']);
        $this->layanan()->simpanSektor($user->id, ['kesehatan']);

        $filter = $this->layanan()->filterBaris($user->id);

        $this->assertTrue($filter(['sektor_kode' => 'kesehatan']));
        $this->assertFalse($filter(['sektor_kode' => 'pendidikan']));
    }

    public function test_pengguna_tanpa_pembatasan_tidak_mendapat_penyaring(): void
    {
        $user = makeRole('analis opd', ['dataspasial-dasbor-read']);

        $this->assertNull($this->layanan()->filterBaris($user->id));
    }

    public function test_pembuatan_sektor_baru_ditolak_untuk_pengguna_yang_dibatasi(): void
    {
        $user = makeRole('admin skema', ['dataspasial-skema-read', 'dataspasial-skema-write']);
        $this->layanan()->simpanSektor($user->id, ['kesehatan']);
        $this->actingAs($user);

        $this->postJson(route('dataspasial.skema.draf.store'), ['sektor_nama' => 'Sektor Baru'])
            ->assertStatus(403);
    }

    public function test_wilayah_dipaksa_pada_permintaan_dasbor(): void
    {
        $user = makeRole('analis opd', ['dataspasial-dasbor-read']);
        $this->layanan()->simpanWilayah($user->id, 'desa', '5102102003');
        $this->actingAs($user);

        // Permintaan mencoba keluar dari wilayah; middleware menggantinya dengan wilayah pengguna.
        $this->getJson(route('dataspasial.dasbor.data', ['kode_desa' => '5102022007', 'kode_kecamatan' => '510202']));

        Http::assertSent(fn ($request) => str_contains($request->url(), 'kode_desa=5102102003')
            && ! str_contains($request->url(), '5102022007'));
    }

    public function test_dropdown_sektor_dasbor_mengikuti_pembatasan(): void
    {
        $user = makeRole('analis opd', ['dataspasial-dasbor-read']);
        $this->layanan()->simpanSektor($user->id, ['pendidikan']);
        $this->actingAs($user);

        $this->get(route('dataspasial.dasbor'))
            ->assertStatus(200)
            ->assertViewHas('sektorList', fn (array $daftar) => count($daftar) === 1 && $daftar[0]['kode'] === 'pendidikan');
    }

    public function test_penjaga_dropdown_ikut_dimuat_di_halaman_dataspasial(): void
    {
        $user = makeRole('analis opd', ['dataspasial-dasbor-read']);
        $this->layanan()->simpanSektor($user->id, ['pendidikan']);
        $this->actingAs($user);

        $this->get(route('dataspasial.dasbor'))
            ->assertStatus(200)
            ->assertSee('"sektor":["pendidikan"]', false);
    }
}
