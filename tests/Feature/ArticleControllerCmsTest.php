<?php

namespace Tests\Feature;

use App\Models\CMS\Article;
use App\Models\CMS\Category;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\BaseTestCase;
use Tests\TestCase;

class ArticleControllerCmsTest extends BaseTestCase
{
    use DatabaseTransactions;
    
    public function test_index_menampilkan_halaman_dengan_status_200()
    {
        $response = $this->get(route('articles.index'));
        $response->assertStatus(200);
    }

    public function test_index_ajax_mengembalikan_json()
    {
        $response = $this->getJson(route('articles.index'), [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $response->assertOk()
                ->assertJsonStructure(['data']);
    }

    public function test_create_menampilkan_form_tambah_artikel()
    {
        $response = $this->get(route('articles.create'));
        $response->assertStatus(200)->assertSee('form'); // asumsi ada tag form
    }

    public function test_store_menyimpan_artikel_baru()
    {
        Storage::fake('public');
        $category = Category::factory()->create();

        $response = $this->post(route('articles.store'), [
            'title' => 'Judul Artikel Baru',
            'slug' => 'judul-artikel-baru',
            'content' => 'Ini adalah isi konten artikel untuk keperluan pengujian unit.',
            'foto' => UploadedFile::fake()->image('thumb.jpg'),
            'category_id' => $category->id,
            'published_at' => now()->format('d/m/Y'),
            'state' => 1,
        ]);

        $response->assertRedirect(route('articles.index'));
        $this->assertDatabaseHas('articles', ['title' => 'Judul Artikel Baru']);
    }


    // public function test_show_menampilkan_artikel()
    // {
    //     $article = Article::factory()->create();

    //     $response = $this->get(route('articles.show', $article->id));
    //     $response->assertStatus(200)->assertSee($article->judul);
    // }

    public function test_edit_menampilkan_form_edit_artikel()
    {
        $article = Article::factory()->create();

        $response = $this->get(route('articles.edit', $article->id));
        $response->assertStatus(200)->assertSee('form');
    }

    public function test_update_memperbarui_artikel()
    {
        $article = Article::factory()->create([
            'title' => 'Lama artikel',
        ]);

        $response = $this->put(route('articles.update', $article->id), [
            'title' => 'Judul Baru',
            'slug' => 'judul-baru',
            'content' => 'Isi artikel setelah diperbarui.',
            'category_id' => $article->category_id,
            'published_at' => now()->format('d/m/Y'),
            'state' => 1,
        ]);

        $response->assertRedirect(route('articles.index'));
        $this->assertDatabaseHas('articles', ['title' => 'Judul Baru']);
    }

    public function test_destroy_menghapus_artikel()
    {
        $article = Article::factory()->create();

        $response = $this->delete(route('articles.destroy', $article->id));
        $response->assertRedirect(route('articles.index'));
        $this->assertSoftDeleted('articles', ['id' => $article->id]);
    }

    public function test_destroy_ajax_menghapus_artikel_dengan_json()
    {
        $article = Article::factory()->create();

        $response = $this->deleteJson(
            route('articles.destroy', $article->id),
            [],
            ['X-Requested-With' => 'XMLHttpRequest']
        );

        $response->assertJson(['success' => true]);
        $this->assertSoftDeleted('articles', ['id' => $article->id]);
    }

}
