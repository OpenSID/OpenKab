<?php

declare(strict_types=1);

namespace Tests\Feature\DataSpasial;

use App\Models\Setting;
use App\Models\User;
use App\Services\CakupanPenggunaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Pembatasan sektor (issue OpenSID/OpenKab#1320) pada Impor Data dan Dasbor DataSpasial.
 */
class SektorAksesImporDasborTest extends TestCase
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

    /** Pengguna impor dengan hak akses sektor tertentu (wilayah dibuat lengkap agar tidak menghalangi uji sektor). */
    private function penggunaDenganSektor(array $sektor): User
    {
        $user = makeRole('operator impor', ['dataspasial-impor-read', 'dataspasial-impor-write', 'dataspasial-dasbor-read']);
        app(CakupanPenggunaService::class)->simpanSektor($user->id, $sektor);

        return $user;
    }

    // ---- Impor Data ----

    public function test_halaman_impor_hanya_menampilkan_sektor_yang_diizinkan(): void
    {
        $user = $this->penggunaDenganSektor(['kesehatan']);
        $this->actingAs($user);

        $this->get(route('dataspasial.impor'))
            ->assertStatus(200)
            ->assertViewHas('sektors', function ($hasil) {
                $kode = array_column(array_column($hasil->data, 'attributes'), 'kode');

                return $kode === ['kesehatan'];
            });
    }

    public function test_unduh_templat_sektor_di_luar_hak_akses_ditolak(): void
    {
        $this->actingAs($this->penggunaDenganSektor(['kesehatan']));

        $this->get(route('dataspasial.impor.template', ['sektor' => 'pendidikan', 'kecamatan' => '510210']))
            ->assertStatus(403);
    }

    public function test_unggah_sektor_di_luar_hak_akses_ditolak(): void
    {
        $this->actingAs($this->penggunaDenganSektor(['kesehatan']));

        $berkas = UploadedFile::fake()->create('impor.xlsx', 10, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->postJson(route('dataspasial.impor.upload'), ['sektor' => 'pendidikan', 'file' => $berkas])
            ->assertStatus(403);
    }

    public function test_pratinjau_dengan_sesi_sektor_di_luar_hak_akses_ditolak(): void
    {
        $this->actingAs($this->penggunaDenganSektor(['kesehatan']));

        $this->withSession(['impor_parsed' => ['sektor' => 'pendidikan', 'filename' => 'a.xlsx', 'rows' => []]])
            ->postJson(route('dataspasial.impor.preview'), [])
            ->assertStatus(403);
    }

    public function test_simpan_dengan_sesi_sektor_di_luar_hak_akses_ditolak(): void
    {
        $this->actingAs($this->penggunaDenganSektor(['kesehatan']));

        $this->withSession(['impor_parsed' => ['sektor' => 'pendidikan', 'filename' => 'a.xlsx', 'rows' => []]])
            ->postJson(route('dataspasial.impor.simpan', ['impor_id' => 'imp001']), [])
            ->assertStatus(403);
    }

    public function test_simpan_tanpa_sesi_unggah_ditolak(): void
    {
        $this->actingAs($this->penggunaDenganSektor(['kesehatan']));

        $this->postJson(route('dataspasial.impor.simpan', ['impor_id' => 'imp001']), [])
            ->assertStatus(403);
    }

    // ---- Dasbor ----

    public function test_dasbor_hanya_menampilkan_sektor_yang_diizinkan(): void
    {
        $this->actingAs($this->penggunaDenganSektor(['pendidikan']));

        $this->get(route('dataspasial.dasbor'))
            ->assertStatus(200)
            ->assertViewHas('sektorList', function (array $daftar) {
                return count($daftar) === 1 && $daftar[0]['kode'] === 'pendidikan';
            });
    }

    public function test_dasbor_data_sektor_di_luar_hak_akses_ditolak(): void
    {
        $this->actingAs($this->penggunaDenganSektor(['pendidikan']));

        $this->getJson(route('dataspasial.dasbor.data', ['sektor' => 'kesehatan']))
            ->assertStatus(403);
    }

    public function test_dasbor_data_tanpa_sektor_dipaksa_ke_sektor_yang_diizinkan(): void
    {
        $this->actingAs($this->penggunaDenganSektor(['pendidikan']));

        $respons = $this->getJson(route('dataspasial.dasbor.data'));

        $respons->assertStatus(200);
        Http::assertSent(fn ($request) => str_contains($request->url(), 'sektor=pendidikan')
            && ! str_contains($request->url(), 'sektor=kesehatan'));
    }

    public function test_dasbor_ekspor_sektor_di_luar_hak_akses_ditolak(): void
    {
        $this->actingAs($this->penggunaDenganSektor(['pendidikan']));

        $this->getJson(route('dataspasial.dasbor.export', ['sektor' => 'kesehatan']))
            ->assertStatus(403);
    }

    public function test_pengguna_tanpa_pembatasan_melihat_semua_sektor_di_dasbor(): void
    {
        // Default bebas: tanpa baris pembatasan, semua sektor tampil.
        $user = makeRole('analis opd', ['dataspasial-dasbor-read']);
        $this->actingAs($user);

        $this->get(route('dataspasial.dasbor'))
            ->assertStatus(200)
            ->assertViewHas('sektorList', fn (array $daftar) => count($daftar) === 2);
    }
}
