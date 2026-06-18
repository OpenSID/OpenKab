<?php

namespace Tests\Feature;

use App\Models\CMS\Article;
use App\Models\CMS\Category;
use App\Models\CMS\Page;
use App\Models\Enums\StatusEnum;
use App\Services\ArtikelService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\BaseTestCase;
use Mews\Purifier\Facades\Purifier;

class XssPreventionTest extends BaseTestCase
{
    use DatabaseTransactions;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_article_content_is_sanitized_on_store()
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $xssPayload = '<p>Normal text</p><script>alert("xss")</script><a href="javascript:alert(1)">Click</a>';

        $response = $this->post(route('articles.store'), [
            'title' => 'Judul Artikel XSS',
            'slug' => 'judul-artikel-xss',
            'content' => $xssPayload,
            'category_id' => $category->id,
            'published_at' => now()->format('d/m/Y'),
            'state' => 1,
        ]);

        $response->assertRedirect(route('articles.index'));
        
        $article = Article::where('slug', 'judul-artikel-xss')->first();
        
        $this->assertNotNull($article);
        $this->assertStringNotContainsString('<script>', $article->content);
        $this->assertStringNotContainsString('javascript:', $article->content);
        $this->assertStringContainsString('<p>Normal text</p>', $article->content);
    }

    public function test_page_content_is_sanitized_on_store()
    {
        Storage::fake('public');

        $xssPayload = '<p>Normal page</p><img src="x" onerror="alert(1)">';

        $response = $this->post(route('pages.store'), [
            'title' => 'Halaman XSS',
            'slug' => 'halaman-xss',
            'content' => $xssPayload,
            'published_at' => now()->format('d/m/Y'),
            'state' => StatusEnum::aktif,
        ]);

        $response->assertRedirect(route('pages.index'));
        
        $page = Page::where('slug', 'halaman-xss')->first();
        
        $this->assertNotNull($page);
        $this->assertStringNotContainsString('onerror', $page->content);
        $this->assertStringContainsString('<p>Normal page</p>', $page->content);
    }

    public function test_artikel_opensid_content_is_sanitized_on_view()
    {
        $this->withoutMiddleware([\App\Http\Middleware\WebsiteEnable::class]);

        // Mock the ArtikelService to return XSS payload
        $mockService = Mockery::mock(ArtikelService::class);
        $mockService->shouldReceive('artikelById')->with(1)->andReturn((object) [
            'id' => 1,
            'judul' => 'Detail Test Artikel XSS',
            'isi' => 'Konten detail <script>alert(1)</script><iframe src="javascript:alert(1)"></iframe>',
            'id_kategori' => 1,
            'kategori_nama' => 'Berita Desa',
            'tgl_upload' => '2023-10-01 10:00:00',
            'enabled' => 1,
        ]);

        $this->app->instance(ArtikelService::class, $mockService);

        $response = $this->get(route('web.artikel.show', ['id' => 1]));
        $response->assertStatus(200);
        $response->assertViewIs('web.artikel.show');
        
        // Assert view does not contain script tags in the output
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertDontSee('javascript:alert(1)', false);
    }
}
