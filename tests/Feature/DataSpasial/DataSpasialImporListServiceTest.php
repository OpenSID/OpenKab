<?php

declare(strict_types=1);

namespace Tests\Feature\DataSpasial;

use App\Models\Setting;
use App\Services\DataSpasialImporListService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class DataSpasialImporListServiceTest extends TestCase
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
    }

    private function fixture(string $nama): array
    {
        return json_decode((string) file_get_contents(__DIR__.'/Fixtures/'.$nama), true, 512, JSON_THROW_ON_ERROR);
    }

    private function layanan(): DataSpasialImporListService
    {
        return app(DataSpasialImporListService::class);
    }

    public function test_baris_diatribusi_ke_desa_lewat_peta_fasilitas(): void
    {
        Http::fake([
            '*/dataspasial/nilai*' => Http::response($this->fixture('nilai-impor.json')),
            '*/dataspasial/fasilitas*' => Http::response($this->fixture('fasilitas.json')),
        ]);

        $baris = $this->layanan()->ambilBaris(6758, '2026-11', null);

        $desaPerFasilitas = collect($baris)->where('fasilitas_id', 691)->first();
        $this->assertSame('5102102003', $desaPerFasilitas['kode_desa']);
        $this->assertSame('Batungsel', $desaPerFasilitas['nama_desa']);
        $this->assertSame('510210', $desaPerFasilitas['kode_kecamatan']);
    }

    public function test_baris_dengan_sumber_selain_impor_dibuang(): void
    {
        Http::fake([
            '*/dataspasial/nilai*' => Http::response($this->fixture('nilai-impor.json')),
            '*/dataspasial/fasilitas*' => Http::response($this->fixture('fasilitas.json')),
        ]);

        $baris = $this->layanan()->ambilBaris(6758, '2026-11', null);

        $this->assertCount(3, $baris);
        $this->assertSame(['impor'], array_values(array_unique(array_column($baris, 'sumber_data'))));
    }

    public function test_nilai_kosong_tampil_null_bukan_nol(): void
    {
        Http::fake([
            '*/dataspasial/nilai*' => Http::response($this->fixture('nilai-impor.json')),
            '*/dataspasial/fasilitas*' => Http::response($this->fixture('fasilitas.json')),
        ]);

        $baris = $this->layanan()->ambilBaris(6758, '2026-11', null);

        $dokter = collect($baris)->firstWhere('isian_kode', 'dokter');
        $this->assertNull($dokter['nilai']);
    }

    public function test_wilayah_pada_nilai_dipakai_lebih_dulu_jika_tersedia(): void
    {
        $nilai = $this->fixture('nilai-impor.json');
        $nilai['data'][0]['attributes']['wilayah'] = [
            'kode_desa' => '9999999999',
            'kode_kecamatan' => '999999',
            'nama_desa' => 'Desa Dari Nilai',
        ];

        Http::fake([
            '*/dataspasial/nilai*' => Http::response($nilai),
            '*/dataspasial/fasilitas*' => Http::response($this->fixture('fasilitas.json')),
        ]);

        $baris = $this->layanan()->ambilBaris(6758, '2026-11', null);

        $this->assertSame('9999999999', collect($baris)->firstWhere('isian_kode', 'bidan')['kode_desa']);
    }

    public function test_fasilitas_tidak_dikenal_tetap_tampil_dengan_desa_null_dan_dicatat(): void
    {
        Log::spy();

        $nilai = $this->fixture('nilai-impor.json');
        $nilai['data'][0]['attributes']['fasilitas_id'] = 123456;

        Http::fake([
            '*/dataspasial/nilai*' => Http::response($nilai),
            '*/dataspasial/fasilitas*' => Http::response($this->fixture('fasilitas.json')),
        ]);

        $baris = $this->layanan()->ambilBaris(6758, '2026-11', null);

        $tanpaDesa = collect($baris)->firstWhere('fasilitas_id', 123456);
        $this->assertNotNull($tanpaDesa);
        $this->assertNull($tanpaDesa['kode_desa']);

        Log::shouldHaveReceived('warning')->atLeast()->once();
    }

    public function test_semua_halaman_nilai_diambil_sampai_total_pages(): void
    {
        $halaman1 = $this->fixture('nilai-impor.json');
        $halaman1['meta']['pagination']['total_pages'] = 2;

        $halaman2 = $this->fixture('nilai-impor.json');
        $halaman2['data'] = [$halaman2['data'][1]];
        $halaman2['meta']['pagination']['current_page'] = 2;
        $halaman2['meta']['pagination']['total_pages'] = 2;

        $halamanDiminta = [];

        Http::fake(function ($request) use ($halaman1, $halaman2, &$halamanDiminta) {
            if (str_contains($request->url(), '/dataspasial/fasilitas')) {
                return Http::response($this->fixture('fasilitas.json'));
            }

            parse_str(parse_url($request->url(), PHP_URL_QUERY) ?? '', $query);
            $nomor = (int) ($query['page']['number'] ?? $query['page[number]'] ?? 1);
            $halamanDiminta[] = $nomor;

            $ukuran = (int) ($query['page']['size'] ?? $query['page[size]'] ?? 0);
            $this->assertSame(100, $ukuran, 'page[size] harus 100 (batas API)');

            return Http::response($nomor === 1 ? $halaman1 : $halaman2);
        });

        $baris = $this->layanan()->ambilBaris(6758, '2026-11', null);

        // Halaman 1: 3 baris impor (1 baris desa dibuang). Halaman 2: 1 baris impor.
        $this->assertSame([1, 2], $halamanDiminta);
        $this->assertCount(4, $baris);
    }

    /** @return array<int, array<string, mixed>> */
    private function barisCampuran(): array
    {
        return [
            ['fasilitas_id' => 691, 'kode_desa' => '5102102003', 'nama_desa' => 'Batungsel', 'kode_kecamatan' => '510210', 'isian_kode' => 'guru', 'isian_label' => 'Guru', 'isian_tipe' => 'angka', 'nilai' => 4, 'periode' => '2026-11', 'sumber_data' => 'impor'],
            ['fasilitas_id' => 691, 'kode_desa' => '5102102003', 'nama_desa' => 'Batungsel', 'kode_kecamatan' => '510210', 'isian_kode' => 'bidan', 'isian_label' => 'Bidan', 'isian_tipe' => 'angka', 'nilai' => 2, 'periode' => '2026-11', 'sumber_data' => 'impor'],
            ['fasilitas_id' => 692, 'kode_desa' => '5102022007', 'nama_desa' => 'Beraban', 'kode_kecamatan' => '510202', 'isian_kode' => 'guru', 'isian_label' => 'Guru', 'isian_tipe' => 'angka', 'nilai' => null, 'periode' => '2026-11', 'sumber_data' => 'impor'],
            ['fasilitas_id' => 692, 'kode_desa' => '5102022007', 'nama_desa' => 'Beraban', 'kode_kecamatan' => '510202', 'isian_kode' => 'guru', 'isian_label' => 'Guru', 'isian_tipe' => 'angka', 'nilai' => 7, 'periode' => '2026-10', 'sumber_data' => 'impor'],
        ];
    }

    public function test_filter_isian_menyaring_baris_secara_lokal(): void
    {
        // API mengabaikan isian_id (lihat permintaan tim API), jadi penyaringan dilakukan di OpenKab.
        $hasil = $this->layanan()->saring($this->barisCampuran(), ['isian_kode' => ['guru']]);

        $this->assertCount(3, $hasil);
        $this->assertSame(['guru'], array_values(array_unique(array_column($hasil, 'isian_kode'))));
    }

    public function test_filter_desa_dan_periode_digabung(): void
    {
        $hasil = $this->layanan()->saring($this->barisCampuran(), [
            'isian_kode' => ['guru'],
            'kode_desa' => '5102022007',
        ]);

        $this->assertCount(2, $hasil);

        $periode = array_values(array_filter($hasil, fn ($b) => $b['periode'] === '2026-11'));
        $this->assertCount(1, $periode);
        $this->assertSame('Beraban', $periode[0]['nama_desa']);
    }

    public function test_ringkasan_dihitung_dari_hasil_filter(): void
    {
        $disaring = $this->layanan()->saring($this->barisCampuran(), ['isian_kode' => ['guru']]);
        $ringkasan = $this->layanan()->ringkasan($disaring);

        $this->assertSame(3, $ringkasan['total_baris']);
        $this->assertSame(2, $ringkasan['total_desa']);
        // Nilai null tidak dihitung sebagai angka.
        $this->assertSame(2, $ringkasan['total_nilai_angka']);
    }
}
