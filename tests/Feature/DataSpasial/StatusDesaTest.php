<?php

declare(strict_types=1);

namespace Tests\Feature\DataSpasial;

use App\Models\Setting;
use App\Services\DataSpasialApiService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(DatabaseTransactions::class);

/**
 * KAB-09 / API-09 — Halaman Status Desa: nonaktifkan/aktifkan pengisian Data Spasial per desa.
 *
 * Fixture mengikuti bentuk respons API Satu Data (`GET /api/v1/dataspasial/desa`,
 * `PUT /api/v1/dataspasial/desa/{config_id}/status`, `DataSpasialDesaTransformer`).
 */
class StatusDesaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['app.databaseGabunganUrl' => 'https://api.example.test', 'dataspasial.cache_ttl' => 300]);
        Setting::query()->updateOrCreate(['key' => 'database_gabungan_api_key'], ['value' => 'test-token']);
        Cache::flush();
    }

    private function actingAsPembaca(): void
    {
        $this->actingAs(makeRole('pembaca status desa uji k09', ['dataspasial-desa-read']));
    }

    private function actingAsPengelola(): void
    {
        $this->actingAs(makeRole('pengelola status desa uji k09', ['dataspasial-desa-read', 'dataspasial-desa-write']));
    }

    /**
     * @return array<string, mixed>
     */
    private function barisDesa(int $configId, string $nama, bool $aktif, ?string $alasan = null): array
    {
        return [
            'type' => 'status isian desa',
            'id' => (string) $configId,
            'attributes' => [
                'config_id' => $configId,
                'kode_desa' => '510201200'.$configId,
                'nama_desa' => $nama,
                'kode_kecamatan' => '510201',
                'nama_kecamatan' => 'Kediri',
                'kode_kabupaten' => '5102',
                'isian_aktif' => $aktif,
                'alasan_nonaktif' => $aktif ? null : $alasan,
                'dinonaktifkan_oleh' => $aktif ? null : 'Budi Santoso',
                'dinonaktifkan_pada' => $aktif ? null : '2026-11-17 09:00:00',
            ],
        ];
    }

    private function fakeDaftar(): void
    {
        Http::fake([
            '*/api/v1/config/*' => Http::response(fakeConfigEndpoint(), 200),
            '*/api/v1/dataspasial/desa*' => Http::response([
                'data' => [
                    $this->barisDesa(1, 'Bajera', true),
                    $this->barisDesa(2, 'Bantas', false, 'Data belum diverifikasi'),
                ],
                'meta' => [
                    'ringkasan_status' => ['total' => 12, 'aktif' => 11, 'nonaktif' => 1],
                    'pagination' => ['total' => 2, 'count' => 2, 'per_page' => 25, 'current_page' => 1, 'total_pages' => 1],
                ],
            ], 200),
            '*' => Http::response(['data' => []], 200),
        ]);
    }

    /**
     * Respons `PUT /dataspasial/desa/status` (massal) sesuai DesaController::ubahStatusMassal.
     *
     * @return array<string, mixed>
     */
    private function hasilMassal(bool $aktif, int $diubah, int $tetap, ?string $kodeKecamatan = null): array
    {
        return [
            'data' => [
                'type' => 'status isian desa massal',
                'attributes' => [
                    'aktif' => $aktif,
                    'kode_kabupaten' => '5102',
                    'kode_kecamatan' => $kodeKecamatan,
                    'jumlah_desa' => $diubah + $tetap,
                    'jumlah_diubah' => $diubah,
                    'jumlah_tidak_berubah' => $tetap,
                ],
            ],
        ];
    }

    // -----------------------------------------------------------------------
    // Akses
    // -----------------------------------------------------------------------

    public function test_guest_is_redirected(): void
    {
        $this->get('/dataspasial/desa')->assertRedirect('/login');
    }

    public function test_user_without_permission_is_rejected(): void
    {
        $tanpaIzin = makeRole('tanpa status desa uji k09', []);

        $this->actingAs($tanpaIzin)->get('/dataspasial/desa')->assertForbidden();
        $this->actingAs($tanpaIzin)->getJson('/dataspasial/desa/data')->assertForbidden();
    }

    public function test_pembaca_tidak_boleh_mengubah_status(): void
    {
        Http::fake();
        $this->actingAsPembaca();

        $this->post('/dataspasial/desa/1/status', ['aktif' => 0, 'alasan' => 'Coba'])->assertForbidden();

        Http::assertNothingSent();
    }

    // -----------------------------------------------------------------------
    // Halaman & data
    // -----------------------------------------------------------------------

    public function test_pembaca_melihat_halaman_tanpa_kolom_aksi(): void
    {
        $this->fakeDaftar();
        $this->actingAsPembaca();

        $this->get('/dataspasial/desa')
            ->assertOk()
            ->assertSee('data-testid="status-desa-filter"', false)
            ->assertSee('data-testid="tabel-status-desa"', false)
            ->assertDontSee('data-testid="modal-status-desa"', false);
    }

    public function test_pengelola_melihat_modal_ubah_status(): void
    {
        $this->fakeDaftar();
        $this->actingAsPengelola();

        $this->get('/dataspasial/desa')
            ->assertOk()
            ->assertSee('data-testid="modal-status-desa"', false)
            ->assertSee('data-testid="input-alasan"', false);
    }

    public function test_data_menormalkan_baris_api(): void
    {
        $this->fakeDaftar();
        $this->actingAsPembaca();

        $resp = $this->getJson('/dataspasial/desa/data?status=nonaktif&kode_kecamatan=510201')->assertOk();

        $this->assertFalse($resp->json('meta.unavailable'));
        $this->assertSame(['total' => 2, 'nonaktif' => 1], $resp->json('ringkasan'));

        $bantas = collect($resp->json('baris'))->firstWhere('config_id', 2);
        $this->assertFalse($bantas['isian_aktif']);
        $this->assertSame('Nonaktif', $bantas['status_label']);
        $this->assertSame('danger', $bantas['warna_lencana']);
        $this->assertSame('Data belum diverifikasi', $bantas['alasan_nonaktif']);
        $this->assertSame('Budi Santoso', $bantas['dinonaktifkan_oleh']);
        $this->assertNotSame('—', $bantas['dinonaktifkan_pada_label']);

        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/api/v1/dataspasial/desa')
            && str_contains(urldecode($r->url()), 'status=nonaktif')
            && str_contains($r->url(), 'kode_kecamatan=510201'));
    }

    public function test_data_tidak_tersedia_tanpa_pesan_teknis(): void
    {
        Http::fake(['*/api/v1/dataspasial/desa*' => Http::response(['message' => 'SQLSTATE rahasia'], 500)]);
        $this->actingAsPembaca();

        $resp = $this->getJson('/dataspasial/desa/data')->assertOk();

        $this->assertTrue($resp->json('meta.unavailable'));
        $this->assertSame([], $resp->json('baris'));
        $this->assertStringNotContainsString('SQLSTATE', $resp->getContent());
    }

    // -----------------------------------------------------------------------
    // Ubah status
    // -----------------------------------------------------------------------

    public function test_nonaktifkan_mengirim_put_ke_api_dan_menginvalidasi_cache(): void
    {
        Http::fake([
            '*/api/v1/dataspasial/desa/2/status' => Http::response(['data' => $this->barisDesa(2, 'Bantas', false, 'Sedang diaudit')], 200),
        ]);
        $this->actingAsPengelola();
        $generasiAwal = app(DataSpasialApiService::class)->cacheGeneration();

        $this->post('/dataspasial/desa/2/status', ['aktif' => 0, 'alasan' => 'Sedang diaudit', 'kode_kecamatan' => '510201'])
            ->assertRedirect(route('dataspasial.desa', ['kode_kecamatan' => '510201']))
            ->assertSessionHas('success', fn (string $pesan) => str_contains($pesan, 'Bantas') && str_contains($pesan, 'dinonaktifkan'));

        Http::assertSent(function (Request $r) {
            return $r->method() === 'PUT'
                && str_ends_with($r->url(), '/api/v1/dataspasial/desa/2/status')
                && $r['aktif'] === false
                && $r['alasan'] === 'Sedang diaudit'
                && $r->hasHeader('X-OpenKab-User-Name');
        });
        $this->assertGreaterThan($generasiAwal, app(DataSpasialApiService::class)->cacheGeneration());
    }

    public function test_aktifkan_tidak_wajib_alasan(): void
    {
        Http::fake([
            '*/api/v1/dataspasial/desa/2/status' => Http::response(['data' => $this->barisDesa(2, 'Bantas', true)], 200),
        ]);
        $this->actingAsPengelola();

        $this->post('/dataspasial/desa/2/status', ['aktif' => 1])
            ->assertRedirect()
            ->assertSessionHas('success', fn (string $pesan) => str_contains($pesan, 'diaktifkan kembali'));

        Http::assertSent(fn (Request $r) => $r->method() === 'PUT' && $r['aktif'] === true && ! isset($r['alasan']));
    }

    public function test_nonaktifkan_tanpa_alasan_ditolak_sebelum_memanggil_api(): void
    {
        Http::fake();
        $this->actingAsPengelola();

        $this->post('/dataspasial/desa/2/status', ['aktif' => 0])
            ->assertSessionHasErrors(['alasan']);

        Http::assertNothingSent();
    }

    public function test_desa_di_luar_kabupaten_ditampilkan_sebagai_galat(): void
    {
        Http::fake([
            '*/api/v1/dataspasial/desa/999/status' => Http::response(['message' => 'Desa dengan config_id 999 tidak ditemukan.'], 404),
        ]);
        $this->actingAsPengelola();

        $this->post('/dataspasial/desa/999/status', ['aktif' => 0, 'alasan' => 'Coba'])
            ->assertRedirect()
            ->assertSessionHas('error', 'Desa tidak ditemukan atau berada di luar kabupaten Anda.');
    }

    public function test_api_gagal_tidak_membocorkan_pesan_teknis(): void
    {
        Http::fake([
            '*/api/v1/dataspasial/desa/2/status' => Http::response(['message' => 'SQLSTATE rahasia'], 500),
        ]);
        $this->actingAsPengelola();

        $this->postJson('/dataspasial/desa/2/status', ['aktif' => 0, 'alasan' => 'Coba'])
            ->assertStatus(503)
            ->assertJsonMissing(['message' => 'SQLSTATE rahasia'])
            ->assertJsonPath('success', false);
    }

    // -----------------------------------------------------------------------
    // Aksi massal (nonaktifkan / aktifkan semua desa)
    // -----------------------------------------------------------------------

    public function test_tombol_massal_hanya_tampil_untuk_pengelola(): void
    {
        $this->fakeDaftar();

        $this->actingAsPembaca();
        $this->get('/dataspasial/desa')
            ->assertOk()
            ->assertDontSee('data-testid="tombol-nonaktifkan-semua"', false)
            ->assertDontSee('data-testid="modal-status-massal"', false);

        $this->actingAsPengelola();
        $html = $this->get('/dataspasial/desa')
            ->assertOk()
            ->assertSee('data-testid="tombol-nonaktifkan-semua"', false)
            ->assertSee('data-testid="tombol-aktifkan-semua"', false)
            ->assertSee('data-testid="modal-status-massal"', false)
            ->assertSee('data-testid="konfirmasi-massal"', false)
            ->getContent();

        // Tombol berada di card Filter, sesudah filter Status Pengisian, dan sebelum tabel desa
        $posStatus = strpos($html, 'data-testid="filter-status"');
        $posAksi = strpos($html, 'data-testid="aksi-massal"');
        $posFormSelesai = strpos($html, '</form>', (int) $posStatus);
        $posTabel = strpos($html, 'data-testid="tabel-status-desa"');
        $this->assertNotFalse($posAksi);
        $this->assertTrue($posStatus < $posAksi && $posAksi < $posFormSelesai && $posFormSelesai < $posTabel);

        // Script ber-versi agar browser tidak memakai status-desa.js lama dari cache
        $this->assertMatchesRegularExpression('#js/dataspasial/status-desa\.js\?v=\d+#', $html);

        // Label tombol pendek, tanpa label kolom; style tombol biasa (bukan outline). Angka hanya di modal.
        $this->assertMatchesRegularExpression('#class="btn btn-sm btn-danger tombol-status-massal"[^>]*>\s*<i class="fas fa-lock"></i> Nonaktifkan Semua\s*</button>#', $html);
        $this->assertMatchesRegularExpression('#class="btn btn-sm btn-success tombol-status-massal"[^>]*>\s*<i class="fas fa-lock-open"></i> Aktifkan Semua\s*</button>#', $html);
        $this->assertStringNotContainsString('Aksi Semua Desa', $html);
        $this->assertStringNotContainsString('btn-outline-danger tombol-status-massal', $html);
    }

    public function test_pembaca_tidak_boleh_menjalankan_aksi_massal(): void
    {
        Http::fake();
        $this->actingAsPembaca();

        $this->post('/dataspasial/desa/status-massal', ['aktif' => 0, 'alasan' => 'Coba', 'konfirmasi' => 1])->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_data_meneruskan_ringkasan_status_seluruh_cakupan(): void
    {
        $this->fakeDaftar();
        $this->actingAsPembaca();

        $this->getJson('/dataspasial/desa/data?kode_kecamatan=510201')
            ->assertOk()
            ->assertJsonPath('ringkasan_status', ['total' => 12, 'aktif' => 11, 'nonaktif' => 1]);
    }

    public function test_api_lama_tanpa_ringkasan_status_menghasilkan_null(): void
    {
        Http::fake([
            '*/api/v1/dataspasial/desa*' => Http::response(['data' => [$this->barisDesa(1, 'Bajera', true)]], 200),
        ]);
        $this->actingAsPembaca();

        $this->getJson('/dataspasial/desa/data')->assertOk()->assertJsonPath('ringkasan_status', null);
    }

    public function test_nonaktifkan_semua_per_kecamatan_mengirim_kode_kecamatan(): void
    {
        Http::fake([
            '*/api/v1/dataspasial/desa/status' => Http::response($this->hasilMassal(false, 10, 2, '510201'), 200),
        ]);
        $this->actingAsPengelola();
        $generasiAwal = app(DataSpasialApiService::class)->cacheGeneration();

        $this->post('/dataspasial/desa/status-massal', [
            'aktif' => 0,
            'alasan' => 'Verifikasi tahunan',
            'kode_kecamatan' => '510201',
            'nama_kecamatan' => 'Kediri',
            'status' => 'aktif',
            'konfirmasi' => 1,
        ])
            ->assertRedirect(route('dataspasial.desa', ['kode_kecamatan' => '510201', 'status' => 'aktif']))
            ->assertSessionHas('success', fn (string $pesan) => str_contains($pesan, '10 desa di Kecamatan Kediri')
                && str_contains($pesan, 'dinonaktifkan')
                && str_contains($pesan, '2 desa sudah nonaktif'));

        Http::assertSent(function (Request $r) {
            return $r->method() === 'PUT'
                && str_ends_with($r->url(), '/api/v1/dataspasial/desa/status')
                && $r['aktif'] === false
                && $r['alasan'] === 'Verifikasi tahunan'
                && $r['kode_kecamatan'] === '510201'
                && $r->hasHeader('X-OpenKab-User-Name');
        });
        $this->assertGreaterThan($generasiAwal, app(DataSpasialApiService::class)->cacheGeneration());
    }

    public function test_tanpa_filter_kecamatan_berlaku_se_kabupaten(): void
    {
        Http::fake([
            '*/api/v1/dataspasial/desa/status' => Http::response($this->hasilMassal(false, 133, 0), 200),
        ]);
        $this->actingAsPengelola();

        $this->post('/dataspasial/desa/status-massal', ['aktif' => 0, 'alasan' => 'Kunci semua', 'konfirmasi' => 1])
            ->assertRedirect(route('dataspasial.desa'))
            ->assertSessionHas('success', fn (string $pesan) => str_contains($pesan, '133 desa se-kabupaten'));

        Http::assertSent(fn (Request $r) => $r->method() === 'PUT' && ! isset($r['kode_kecamatan']));
    }

    public function test_aktifkan_semua_tidak_wajib_alasan(): void
    {
        Http::fake([
            '*/api/v1/dataspasial/desa/status' => Http::response($this->hasilMassal(true, 5, 0, '510201'), 200),
        ]);
        $this->actingAsPengelola();

        $this->post('/dataspasial/desa/status-massal', ['aktif' => 1, 'kode_kecamatan' => '510201', 'nama_kecamatan' => 'Kediri', 'konfirmasi' => 1])
            ->assertRedirect()
            ->assertSessionHas('success', fn (string $pesan) => str_contains($pesan, '5 desa di Kecamatan Kediri telah diaktifkan kembali'));

        Http::assertSent(fn (Request $r) => $r->method() === 'PUT' && $r['aktif'] === true && ! isset($r['alasan']));
    }

    public function test_aksi_massal_tanpa_alasan_atau_konfirmasi_ditolak_sebelum_memanggil_api(): void
    {
        Http::fake();
        $this->actingAsPengelola();

        $this->post('/dataspasial/desa/status-massal', ['aktif' => 0, 'konfirmasi' => 1])
            ->assertSessionHasErrors(['alasan']);
        $this->post('/dataspasial/desa/status-massal', ['aktif' => 0, 'alasan' => 'Coba'])
            ->assertSessionHasErrors(['konfirmasi']);
        $this->post('/dataspasial/desa/status-massal', ['aktif' => 1, 'kode_kecamatan' => "51'02", 'konfirmasi' => 1])
            ->assertSessionHasErrors(['kode_kecamatan']);

        Http::assertNothingSent();
    }

    public function test_cakupan_kosong_ditampilkan_sebagai_galat(): void
    {
        Http::fake([
            '*/api/v1/dataspasial/desa/status' => Http::response(['message' => 'Tidak ada desa pada cakupan yang dipilih.'], 404),
        ]);
        $this->actingAsPengelola();

        $this->post('/dataspasial/desa/status-massal', ['aktif' => 0, 'alasan' => 'Coba', 'kode_kecamatan' => '999999', 'konfirmasi' => 1])
            ->assertRedirect()
            ->assertSessionHas('error', 'Tidak ada desa pada cakupan yang dipilih.');
    }

    public function test_aksi_massal_api_gagal_tidak_membocorkan_pesan_teknis(): void
    {
        Http::fake([
            '*/api/v1/dataspasial/desa/status' => Http::response(['message' => 'SQLSTATE rahasia'], 500),
        ]);
        $this->actingAsPengelola();

        $this->postJson('/dataspasial/desa/status-massal', ['aktif' => 0, 'alasan' => 'Coba', 'konfirmasi' => 1])
            ->assertStatus(503)
            ->assertJsonMissing(['message' => 'SQLSTATE rahasia'])
            ->assertJsonPath('success', false);
    }
}
