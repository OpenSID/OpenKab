<?php

namespace Tests\Feature;

use App\Models\CMS\Visit;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\BaseTestCase;

class StatistikPengunjungCmsControllerTest extends BaseTestCase
{
    use DatabaseTransactions;

    /** @test */
    public function halaman_statistik_pengunjung_dapat_diakses()
    {
        // Arrange: siapkan data kunjungan palsu
        Visit::factory()->create([
            'device' => 'Desktop',
            'ip' => '127.0.0.1',
            'url' => '/home',
            'created_at' => now(),
        ]);

        Visit::factory()->create([
            'device' => 'Mobile',
            'ip' => '127.0.0.2',
            'url' => '/about',
            'created_at' => now()->subDay(),
        ]);

        // Act: akses route controller
        $response = $this->get(route('cms.statistic.summary'));

        // Assert: pastikan halaman dapat diakses dan ada elemen penting
        $response->assertStatus(200);

        // Periksa apakah teks judul chart muncul di halaman (konten Blade)
        $response->assertSee('Jumlah kunjungan berdasarkan gawai');
        $response->assertSee('Jumlah kunjungan harian');
        $response->assertSee('Jumlah kunjungan berdasarkan url');
    }
}
