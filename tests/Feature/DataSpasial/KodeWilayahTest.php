<?php

declare(strict_types=1);

namespace Tests\Feature\DataSpasial;

use App\Models\Setting;
use App\Models\UserRegionAccess;
use App\Services\CakupanPenggunaService;
use App\Support\KodeWilayah;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Helper kode wilayah (App\Support\KodeWilayah) dan penerapannya pada hak akses wilayah.
 */
class KodeWilayahTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.databaseGabunganUrl' => 'https://api.example.test']);
        Setting::query()->updateOrCreate(
            ['key' => 'database_gabungan_api_key'],
            ['value' => 'test-token']
        );
        Cache::flush();
        fakeConfigEndpoint();
        Http::fake(['*/dataspasial/sektor*' => Http::response(['data' => []])]);
    }

    public function test_kode_desa_dipotong_menjadi_kecamatan_dan_kabupaten(): void
    {
        // Contoh dari pengguna: 5102 = kabupaten, 510210 = kecamatan, 5102102009 = desa.
        $this->assertSame('5102', KodeWilayah::kabupaten('5102102009'));
        $this->assertSame('510210', KodeWilayah::kecamatan('5102102009'));
        $this->assertSame('5102', KodeWilayah::kabupaten('510210'));
        $this->assertSame('510210', KodeWilayah::kecamatan('510210'));
    }

    public function test_kode_terlalu_pendek_tidak_menghasilkan_kode_induk(): void
    {
        $this->assertNull(KodeWilayah::kecamatan('5102'));
        $this->assertNull(KodeWilayah::kabupaten('51'));
        $this->assertNull(KodeWilayah::kabupaten(null));
    }

    public function test_level_ditentukan_dari_panjang_kode(): void
    {
        $this->assertSame('kabupaten', KodeWilayah::levelDari('5102'));
        $this->assertSame('kecamatan', KodeWilayah::levelDari('510210'));
        $this->assertSame('desa', KodeWilayah::levelDari('5102102009'));
        $this->assertNull(KodeWilayah::levelDari('51021'));
        $this->assertNull(KodeWilayah::levelDari('5102ab'));
    }

    public function test_sesuai_level_menolak_panjang_yang_salah(): void
    {
        $this->assertTrue(KodeWilayah::sesuaiLevel('desa', '5102102009'));
        $this->assertFalse(KodeWilayah::sesuaiLevel('desa', '510210'));
        $this->assertFalse(KodeWilayah::sesuaiLevel('kecamatan', '5102102009'));
    }

    public function test_pembatasan_desa_menyimpan_kode_desa_lengkap_dan_menurunkan_induknya(): void
    {
        $user = makeRole('analis opd', ['dataspasial-dasbor-read']);
        $layanan = app(CakupanPenggunaService::class);

        $layanan->simpanWilayah($user->id, 'desa', '5102102009');

        $this->assertSame('5102102009', UserRegionAccess::where('user_id', $user->id)->value('region_code'));
        $this->assertSame(
            ['kode_kabupaten' => '5102', 'kode_kecamatan' => '510210', 'kode_desa' => '5102102009'],
            $layanan->filterDasbor(['level' => 'desa', 'kode' => '5102102009']),
        );
        $this->assertTrue($layanan->kecamatanDalamWilayah(['level' => 'desa', 'kode' => '5102102009'], '510210'));
        $this->assertFalse($layanan->kecamatanDalamWilayah(['level' => 'desa', 'kode' => '5102102009'], '510202'));
    }

    public function test_pembatasan_desa_hanya_mengizinkan_baris_desa_itu(): void
    {
        $layanan = app(CakupanPenggunaService::class);
        $filter = $layanan->filterBaris(
            makeRole('analis opd', ['dataspasial-dasbor-read'])->id,
            false,
        );
        $this->assertNull($filter, 'Tanpa pembatasan, tidak ada penyaring.');

        $user = makeRole('analis opd', ['dataspasial-dasbor-read']);
        $layanan->simpanWilayah($user->id, 'desa', '5102102009');
        $filter = $layanan->filterBaris($user->id, false);

        $this->assertTrue($filter(['kode_desa' => '5102102009']));
        $this->assertFalse($filter(['kode_desa' => '5102102008']), 'Desa lain di kecamatan yang sama tidak boleh.');
        $this->assertFalse($filter(['kode_desa' => '5102022007']));
    }

    public function test_form_pengguna_menolak_kode_dengan_panjang_tidak_sesuai_level(): void
    {
        $admin = makeRole('administrator', ['pengaturan-users-read', 'pengaturan-users-edit']);
        $target = makeRole('operator impor', ['dataspasial-impor-read']);
        $this->actingAs($admin);

        $this->put(route('users.update', $target->id), [
            'name' => 'Uji Pengguna',
            'username' => 'uji'.$target->id,
            'email' => 'uji'.$target->id.'@example.test',
            'group' => $target->team->first()->id,
            'akses_form' => 1,
            'akses_wilayah_aktif' => 1,
            'akses_wilayah_level' => 'desa',
            'akses_wilayah_desa' => '510210', // kecamatan, bukan desa
        ])->assertSessionHas('error', 'Kode desa tidak valid: harus 10 digit.');

        $this->assertSame(0, UserRegionAccess::where('user_id', $target->id)->count());
    }
}
