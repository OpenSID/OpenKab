<?php

declare(strict_types=1);

namespace Tests\Feature\DataSpasial;

use App\Models\Setting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Filter dari URL halaman audit (mis. ?kode_desa=5102102003) dipilihkan di dropdown; kecamatan diturunkan dari desa.
 */
class AuditFilterDariUrlTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.databaseGabunganUrl' => 'https://api.example.test']);
        Setting::query()->updateOrCreate(['key' => 'database_gabungan_api_key'], ['value' => 'test-token']);
        Cache::flush();
        fakeConfigEndpoint();
        Http::fake(['*/dataspasial/sektor*' => Http::response(['data' => []])]);
    }

    public function test_kode_desa_dari_url_dipilihkan_beserta_kecamatan_induknya(): void
    {
        $admin = makeRole('administrator', ['dataspasial-audit-read']);
        $this->actingAs($admin);

        $this->get(route('dataspasial.audit', ['kode_desa' => '5102102003']))
            ->assertStatus(200)
            ->assertViewHas('kodeDesaTerpilih', '5102102003')
            ->assertViewHas('kodeKecamatanTerpilih', '510210');
    }

    public function test_kode_kecamatan_dari_url_dipakai_apa_adanya(): void
    {
        $admin = makeRole('administrator', ['dataspasial-audit-read']);
        $this->actingAs($admin);

        $this->get(route('dataspasial.audit', ['kode_kecamatan' => '510202']))
            ->assertStatus(200)
            ->assertViewHas('kodeKecamatanTerpilih', '510202')
            ->assertViewHas('kodeDesaTerpilih', '');
    }

    public function test_nilai_url_yang_bukan_kode_diabaikan(): void
    {
        $admin = makeRole('administrator', ['dataspasial-audit-read']);
        $this->actingAs($admin);

        $this->get(route('dataspasial.audit', ['kode_desa' => "5102'; DROP"]))
            ->assertStatus(200)
            ->assertViewHas('kodeDesaTerpilih', '');
    }
}
