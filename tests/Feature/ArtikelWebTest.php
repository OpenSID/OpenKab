<?php

namespace Tests\Feature;

use App\Models\CMS\Article;
use App\Models\CMS\Category;
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
        $mockService->shouldReceive('getCombinedArticles')->andReturn(collect([
            (object) [
                'id' => 1,
                'judul' => 'Test Artikel OpenSID',
                'isi' => 'Konten artikel test',
                'id_kategori' => 1,
                'kategori_nama' => 'Berita Desa',
                'tgl_upload' => '2023-10-01 10:00:00',
                'enabled' => 1,
                'source' => 'opensid',
                'detail_url' => route('web.artikel.show', 1),
            ]
        ]));

        $this->app->instance(ArtikelService::class, $mockService);

        $response = $this->get(route('web.artikel.index'));
        $response->assertStatus(200);
        $response->assertViewIs('web.artikel.index');
        $response->assertSee('Artikel Berita');
        $response->assertSee('Test Artikel OpenSID');
        $response->assertSee('Berita Desa');
    }

    #[Test]
    public function it_displays_openkab_cms_articles_in_public_index()
    {
        $this->withoutMiddleware([\App\Http\Middleware\WebsiteEnable::class]);

        $category = Category::factory()->create(['name' => 'Kategori OpenKab']);
        $localArticle = Article::factory()->create([
            'category_id' => $category->id,
            'title' => 'Judul Artikel OpenKab CMS',
            'content' => 'Konten artikel cms openkab lokal',
            'state' => Article::PUBLISH,
            'published_at' => now()->subDay(),
        ]);

        $response = $this->get(route('web.artikel.index'));
        $response->assertStatus(200);
        $response->assertSee('Judul Artikel OpenKab CMS');
        $response->assertSee('OpenKab');
    }

    #[Test]
    public function it_can_access_artikel_terbaru_json_endpoint()
    {
        $this->withoutMiddleware([\App\Http\Middleware\WebsiteEnable::class]);

        $category = Category::factory()->create(['name' => 'Kategori OpenKab']);
        $localArticle = Article::factory()->create([
            'category_id' => $category->id,
            'title' => 'Berita Terkini OpenKab',
            'content' => 'Cuplikan berita terkini',
            'state' => Article::PUBLISH,
            'published_at' => now(),
        ]);

        $response = $this->get(route('web.artikel.terbaru'));
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'data',
        ]);
        $response->assertSee('Berita Terkini OpenKab');
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
    public function it_redirects_to_local_cms_article_if_id_matches_local_article()
    {
        $this->withoutMiddleware([\App\Http\Middleware\WebsiteEnable::class]);

        $category = Category::factory()->create();
        $localArticle = Article::factory()->create([
            'category_id' => $category->id,
            'title' => 'Artikel Lokal Redirect',
            'slug' => 'artikel-lokal-redirect',
            'state' => Article::PUBLISH,
            'published_at' => now()->subDay(),
        ]);

        $mockService = Mockery::mock(ArtikelService::class);
        $mockService->shouldReceive('artikelById')->with($localArticle->id)->andReturn(null);
        $this->app->instance(ArtikelService::class, $mockService);

        $response = $this->get(route('web.artikel.show', ['id' => $localArticle->id]));
        $response->assertRedirect(route('article', ['aSlug' => $localArticle->slug]));
    }

    #[Test]
    public function it_aborts_404_for_disabled_or_missing_artikel()
    {
        $this->withoutMiddleware([\App\Http\Middleware\WebsiteEnable::class]);

        // Mock the ArtikelService
        $mockService = Mockery::mock(ArtikelService::class);
        $mockService->shouldReceive('artikelById')->with(999999)->andReturn(null);

        $mockService->shouldReceive('artikelById')->with(2)->andReturn((object) [
            'id' => 2,
            'judul' => 'Hidden Artikel',
            'enabled' => 0,
        ]);

        $this->app->instance(ArtikelService::class, $mockService);

        // Test non-existent article
        $response404 = $this->get(route('web.artikel.show', ['id' => 999999]));
        $response404->assertStatus(404);

        // Test disabled article
        $responseDisabled = $this->get(route('web.artikel.show', ['id' => 2]));
        $responseDisabled->assertStatus(404);
    }
}

