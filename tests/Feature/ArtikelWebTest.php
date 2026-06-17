<?php

namespace Tests\Feature;

use App\Services\ArtikelService;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\BaseTestCase;

class ArtikelWebTest extends BaseTestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    #[Test]
    public function it_can_access_public_artikel_index()
    {
        $this->withoutMiddleware([\App\Http\Middleware\WebsiteEnable::class]);

        // Mock the ArtikelService
        $mockService = Mockery::mock(ArtikelService::class);
        $mockService->shouldReceive('artikel')->andReturn(collect([
            (object) [
                'id' => 1,
                'judul' => 'Test Artikel OpenSID',
                'isi' => 'Konten artikel test',
                'id_kategori' => 1,
                'kategori_nama' => 'Berita Desa',
                'tgl_upload' => '2023-10-01 10:00:00',
                'enabled' => 1,
            ]
        ]));

        $this->app->instance(ArtikelService::class, $mockService);

        $response = $this->get(route('web.artikel.index'));
        $response->dump();
        $response->assertStatus(200);
        $response->assertViewIs('web.artikel.index');
        $response->assertSee('Artikel Berita');
        $response->assertSee('Test Artikel OpenSID');
        $response->assertSee('Berita Desa');
    }

    #[Test]
    public function it_can_access_public_artikel_show()
    {
        $this->withoutMiddleware([\App\Http\Middleware\WebsiteEnable::class]);

        // Mock the ArtikelService
        $mockService = Mockery::mock(ArtikelService::class);
        $mockService->shouldReceive('artikelById')->with(1)->andReturn((object) [
            'id' => 1,
            'judul' => 'Detail Test Artikel',
            'isi' => 'Konten detail artikel test',
            'id_kategori' => 1,
            'kategori_nama' => 'Berita Desa',
            'tgl_upload' => '2023-10-01 10:00:00',
            'enabled' => 1,
        ]);

        $this->app->instance(ArtikelService::class, $mockService);

        $response = $this->get(route('web.artikel.show', ['id' => 1]));
        $response->assertStatus(200);
        $response->assertViewIs('web.artikel.show');
        $response->assertSee('Detail Test Artikel');
        $response->assertSee('Konten detail artikel test');
    }

    #[Test]
    public function it_aborts_404_for_disabled_or_missing_artikel()
    {
        $this->withoutMiddleware([\App\Http\Middleware\WebsiteEnable::class]);

        // Mock the ArtikelService
        $mockService = Mockery::mock(ArtikelService::class);
        $mockService->shouldReceive('artikelById')->with(99)->andReturn(null);

        $mockService->shouldReceive('artikelById')->with(2)->andReturn((object) [
            'id' => 2,
            'judul' => 'Hidden Artikel',
            'enabled' => 0,
        ]);

        $this->app->instance(ArtikelService::class, $mockService);

        // Test non-existent article
        $response404 = $this->get(route('web.artikel.show', ['id' => 99]));
        $response404->assertStatus(404);

        // Test disabled article
        $responseDisabled = $this->get(route('web.artikel.show', ['id' => 2]));
        $responseDisabled->assertStatus(404);
    }
}
