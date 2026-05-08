<?php

namespace Tests\Feature;

use App\Models\CMS\Article;
use App\Models\CMS\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\BaseTestCase;

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

    // =========================================================
    // Test Pengecekan Kategori
    // =========================================================

    /**
     * Halaman create harus mengirimkan variabel $categories ke view.
     */
    public function test_create_mengirim_variabel_categories_ke_view()
    {
        Category::factory()->create(); // pastikan ada minimal 1 kategori

        $response = $this->get(route('articles.create'));

        $response->assertStatus(200)
                 ->assertViewHas('categories');
    }

    /**
     * Ketika tidak ada kategori, halaman create harus menampilkan
     * script peringatan SweetAlert agar pengguna diarahkan membuat kategori.
     */
    public function test_create_menampilkan_peringatan_ketika_kategori_kosong()
    {
        // Hapus semua kategori agar kondisi kosong tercapai
        Category::query()->forceDelete();

        $response = $this->get(route('articles.create'));

        $response->assertStatus(200)
                 ->assertSee('Kategori Belum Tersedia');
    }

    /**
     * Store harus gagal validasi jika category_id tidak dikirim.
     */
    public function test_store_gagal_validasi_tanpa_category_id()
    {
        $response = $this->post(route('articles.store'), [
            'title'        => 'Artikel Tanpa Kategori',
            'slug'         => 'artikel-tanpa-kategori',
            'content'      => 'Isi konten artikel.',
            'published_at' => now()->format('d/m/Y'),
            'state'        => 1,
            // 'category_id' sengaja tidak dikirim
        ]);

        $response->assertSessionHasErrors('category_id');
        $this->assertDatabaseMissing('articles', ['title' => 'Artikel Tanpa Kategori']);
    }
}
