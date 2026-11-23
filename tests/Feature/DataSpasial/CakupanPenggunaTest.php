<?php

declare(strict_types=1);

namespace Tests\Feature\DataSpasial;

use App\Models\Setting;
use App\Models\User;
use App\Models\UserRegionAccess;
use App\Models\UserSectorAccess;
use App\Services\CakupanPenggunaService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Hak akses wilayah dan sektor (default bebas; pembatasan opsional, diatur di halaman users).
 */
class CakupanPenggunaTest extends TestCase
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

    private function layanan(): CakupanPenggunaService
    {
        return app(CakupanPenggunaService::class);
    }

    private function fixture(string $nama): array
    {
        return json_decode((string) file_get_contents(__DIR__.'/Fixtures/'.$nama), true, 512, JSON_THROW_ON_ERROR);
    }

    private function fakeApi(): void
    {
        Http::fake([
            '*/dataspasial/nilai*' => Http::response($this->fixture('nilai-impor.json')),
            '*/dataspasial/fasilitas*' => Http::response($this->fixture('fasilitas.json')),
            '*/dataspasial/sektor*' => Http::response(['data' => [
                ['type' => 'daftar sektor', 'id' => '6758', 'attributes' => ['kode' => 'kesehatan', 'nama' => 'kesehatan', 'status' => 'aktif']],
                ['type' => 'daftar sektor', 'id' => '6757', 'attributes' => ['kode' => 'pendidikan', 'nama' => 'Pendidikan', 'status' => 'aktif']],
            ], 'meta' => ['pagination' => ['total' => 2, 'count' => 2, 'per_page' => 30, 'current_page' => 1, 'total_pages' => 1]]]),
        ]);
    }

    private function operatorImpor(): User
    {
        return makeRole('operator impor', ['dataspasial-impor-read']);
    }

    private function adminPengaturan(): User
    {
        // Middleware easyauthorize memeriksa `pengaturan-users-read` (index) dan `pengaturan-users-edit` (edit/update).
        return makeRole('administrator', ['pengaturan-users-read', 'pengaturan-users-edit', 'dataspasial-impor-read']);
    }

    // ---- Default bebas ----

    public function test_tanpa_pembatasan_semua_wilayah_dan_sektor_diizinkan(): void
    {
        $user = $this->operatorImpor();

        $this->assertNull($this->layanan()->wilayahAkses($user->id));
        $this->assertNull($this->layanan()->sektorAkses($user->id));
        $this->assertTrue($this->layanan()->bolehSektor($user->id, 'apa_saja'));

        $baris = [['kode_desa' => '5102102003', 'kode_kecamatan' => '510210']];
        $this->assertSame($baris, $this->layanan()->saringWilayah($baris, null));
    }

    // ---- Wilayah: satu pilihan bertingkat ----

    public function test_simpan_wilayah_hanya_menyisakan_satu_baris(): void
    {
        $user = $this->operatorImpor();
        $layanan = $this->layanan();

        $layanan->simpanWilayah($user->id, 'kabupaten', '5102');
        $this->assertSame(['level' => 'kabupaten', 'kode' => '5102'], $layanan->wilayahAkses($user->id));

        $layanan->simpanWilayah($user->id, 'desa', '5102102003');
        $this->assertSame(['level' => 'desa', 'kode' => '5102102003'], $layanan->wilayahAkses($user->id));
        $this->assertSame(1, UserRegionAccess::where('user_id', $user->id)->count());

        $layanan->simpanWilayah($user->id, null, null);
        $this->assertNull($layanan->wilayahAkses($user->id));
    }

    public function test_level_wilayah_menyaring_dengan_benar(): void
    {
        $baris = [
            ['kode_desa' => '5102102003', 'kode_kecamatan' => '510210'],
            ['kode_desa' => '5102022007', 'kode_kecamatan' => '510202'],
            ['kode_desa' => '5199000001', 'kode_kecamatan' => '519900'],
        ];
        $layanan = $this->layanan();

        $kabupaten = $layanan->saringWilayah($baris, ['level' => 'kabupaten', 'kode' => '5102']);
        $this->assertCount(2, $kabupaten);

        $kecamatan = $layanan->saringWilayah($baris, ['level' => 'kecamatan', 'kode' => '510210']);
        $this->assertSame(['5102102003'], array_column($kecamatan, 'kode_desa'));

        $desa = $layanan->saringWilayah($baris, ['level' => 'desa', 'kode' => '5102022007']);
        $this->assertSame(['5102022007'], array_column($desa, 'kode_desa'));
    }

    public function test_filter_dasbor_mengikuti_level(): void
    {
        $layanan = $this->layanan();

        $this->assertSame(
            ['kode_kabupaten' => '5102', 'kode_kecamatan' => '510210', 'kode_desa' => '5102102003'],
            $layanan->filterDasbor(['level' => 'desa', 'kode' => '5102102003']),
        );
        $this->assertSame(
            ['kode_kabupaten' => '5102', 'kode_kecamatan' => null, 'kode_desa' => null],
            $layanan->filterDasbor(['level' => 'kabupaten', 'kode' => '5102']),
        );
    }

    public function test_kecamatan_dropdown_mengikuti_level_desa_melalui_kecamatan_induk(): void
    {
        $layanan = $this->layanan();

        $this->assertTrue($layanan->kecamatanDalamWilayah(['level' => 'desa', 'kode' => '5102102003'], '510210'));
        $this->assertFalse($layanan->kecamatanDalamWilayah(['level' => 'desa', 'kode' => '5102102003'], '510202'));
    }

    // ---- Sektor ----

    public function test_simpan_sektor_membatasi_dan_daftar_kosong_berarti_bebas(): void
    {
        $user = $this->operatorImpor();
        $layanan = $this->layanan();

        $layanan->simpanSektor($user->id, ['kesehatan']);
        $this->assertSame(['kesehatan'], $layanan->sektorAkses($user->id));
        $this->assertTrue($layanan->bolehSektor($user->id, 'kesehatan'));
        $this->assertFalse($layanan->bolehSektor($user->id, 'pendidikan'));

        $layanan->simpanSektor($user->id, []);
        $this->assertNull($layanan->sektorAkses($user->id));
        $this->assertSame(0, UserSectorAccess::where('user_id', $user->id)->count());
    }

    // ---- Endpoint daftar hasil impor ----

    public function test_pengguna_bebas_melihat_seluruh_data(): void
    {
        $this->fakeApi();
        $this->actingAs($this->operatorImpor());

        $this->getJson(route('dataspasial.impor.daftar.data', ['sektor_id' => 6758]))
            ->assertStatus(200)
            ->assertJsonPath('meta.pagination.total', 3);
    }

    public function test_pembatasan_kabupaten_dan_kecamatan_menyaring_data(): void
    {
        $this->fakeApi();
        $user = $this->operatorImpor();
        $this->layanan()->simpanWilayah($user->id, 'kecamatan', '510210');
        $this->actingAs($user);

        $respons = $this->getJson(route('dataspasial.impor.daftar.data', ['sektor_id' => 6758]));

        $respons->assertStatus(200);
        $this->assertSame(['510210'], array_values(array_unique($respons->json('data.*.kode_kecamatan'))));
        $this->assertSame(1, $respons->json('meta.pagination.total'));
    }

    public function test_sektor_di_luar_pembatasan_mendapat_403(): void
    {
        $this->fakeApi();
        $user = $this->operatorImpor();
        $this->layanan()->simpanSektor($user->id, ['pendidikan']);
        $this->actingAs($user);

        $this->getJson(route('dataspasial.impor.daftar.data', ['sektor_id' => 6758]))->assertStatus(403);
    }

    public function test_dropdown_sektor_mengikuti_pembatasan(): void
    {
        $this->fakeApi();
        $user = $this->operatorImpor();
        $this->layanan()->simpanSektor($user->id, ['kesehatan']);
        $this->actingAs($user);

        $this->get(route('dataspasial.impor.daftar'))
            ->assertStatus(200)
            ->assertViewHas('sektors', fn (array $sektors) => count($sektors) === 1 && $sektors[0]['attributes']['kode'] === 'kesehatan');
    }

    // ---- Form pengguna di halaman users ----

    public function test_halaman_edit_pengguna_memuat_bagian_pembatasan(): void
    {
        $this->fakeApi();
        $this->actingAs($this->adminPengaturan());
        $target = $this->operatorImpor();

        $this->get(route('users.edit', $target->id))
            ->assertStatus(200)
            ->assertSee('Batasi hak akses wilayah')
            ->assertSee('Batasi hak akses sektor')
            // Semua input yang dibaca server harus punya name, jika tidak nilainya tidak terkirim.
            ->assertSee('name="akses_wilayah_aktif"', false)
            ->assertSee('name="akses_wilayah_level"', false)
            ->assertSee('name="akses_wilayah_kabupaten"', false)
            ->assertSee('name="akses_wilayah_kecamatan"', false)
            ->assertSee('name="akses_wilayah_desa"', false)
            ->assertSee('name="akses_sektor_aktif"', false)
            ->assertSee('name="akses_sektor[]"', false);
    }

    public function test_admin_menyimpan_pembatasan_wilayah_dan_sektor_dari_form_pengguna(): void
    {
        $this->fakeApi();
        $this->actingAs($this->adminPengaturan());
        $target = $this->operatorImpor();

        $this->put(route('users.update', $target->id), $this->payloadPengguna($target, [
            'akses_form' => 1,
            'akses_wilayah_aktif' => 1,
            'akses_wilayah_level' => 'kecamatan',
            'akses_wilayah_kecamatan' => '510210',
            'akses_sektor_aktif' => 1,
            'akses_sektor' => ['kesehatan'],
        ]))->assertRedirect(route('users.index'));

        $layanan = $this->layanan();
        $this->assertSame(['level' => 'kecamatan', 'kode' => '510210'], $layanan->wilayahAkses($target->id));
        $this->assertSame(['kesehatan'], $layanan->sektorAkses($target->id));
    }

    public function test_pembatasan_dimatikan_mengembalikan_pengguna_ke_bebas(): void
    {
        $this->fakeApi();
        $this->actingAs($this->adminPengaturan());
        $target = $this->operatorImpor();
        $this->layanan()->simpanWilayah($target->id, 'kabupaten', '5102');
        $this->layanan()->simpanSektor($target->id, ['kesehatan']);

        $this->put(route('users.update', $target->id), $this->payloadPengguna($target, [
            'akses_form' => 1,
        ]))->assertRedirect(route('users.index'));

        $this->assertNull($this->layanan()->wilayahAkses($target->id));
        $this->assertNull($this->layanan()->sektorAkses($target->id));
    }

    public function test_pembatasan_wilayah_tanpa_pilihan_ditolak_dan_tidak_menyimpan(): void
    {
        $this->fakeApi();
        $this->actingAs($this->adminPengaturan());
        $target = $this->operatorImpor();
        $this->layanan()->simpanSektor($target->id, ['kesehatan']);

        $this->put(route('users.update', $target->id), $this->payloadPengguna($target, [
            'akses_form' => 1,
            'akses_wilayah_aktif' => 1,
            'akses_wilayah_level' => 'desa',
        ]))->assertSessionHas('error');

        $this->assertNull($this->layanan()->wilayahAkses($target->id));
        $this->assertSame(['kesehatan'], $this->layanan()->sektorAkses($target->id));
    }

    /**
     * Payload minimal yang lolos UserRequest, ditambah bagian pembatasan.
     *
     * @param  array<string, mixed>  $tambahan
     * @return array<string, mixed>
     */
    private function payloadPengguna(User $target, array $tambahan): array
    {
        $teamId = $target->team->first()->id;

        return array_merge([
            'name' => 'Uji Pengguna',
            'username' => 'uji'.$target->id,
            'email' => 'uji'.$target->id.'@example.test',
            'group' => $teamId,
        ], $tambahan);
    }
}
