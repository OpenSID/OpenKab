<?php

namespace Tests\Feature;

use App\Models\CMS\Article;
use App\Models\CMS\Category;
use App\Models\CMS\Page;
use App\Services\SitemapService;
use Mockery;
use Tests\WebsiteTestCase;

class PageControllerWebsiteTest extends WebsiteTestCase
{
    /** @test */
    public function it_can_access_index_page()
    {
        $response = $this->get(route('web.index'));
        $response->assertStatus(200);
        $response->assertViewIs('web.index');
    }

    /** @test */
    public function it_can_access_category_page()
    {
        $category = Category::factory()->create();
        $response = $this->get(route('category', $category->slug));
        $response->assertStatus(200);
        $response->assertViewIs('web.articles');
        $response->assertViewHas('title', $category->name);
    }

    /** @test */
    public function it_can_access_page_detail()
    {
        $page = Page::factory()->create();
        $response = $this->get(route('page', $page->slug));
        $response->assertStatus(200);
        $response->assertViewIs('web.page');
        $response->assertViewHas('object', $page);
    }

    /** @test */
    public function it_can_access_article_detail()
    {
        $article = Article::factory()->create();
        $response = $this->get(route('article', $article->slug));
        $response->assertStatus(200);
        $response->assertViewIs('web.article');
        $response->assertViewHas('object', $article);
    }

    /** @test */
    public function it_can_access_sitemap()
    {
        $mock = Mockery::mock(SitemapService::class);
        $mock->shouldReceive('render')->once()->andReturn('sitemap-content');
        $this->app->instance(SitemapService::class, $mock);

        $response = $this->get(route('sitemap'));
        $response->assertStatus(200);
        $response->assertSee('sitemap-content');
    }
}
