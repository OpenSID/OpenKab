<?php

namespace Tests\Feature;

use App\Models\CMS\Article;
use App\Models\CMS\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\BaseTestCase;

class CategoryControllerCmsTest extends BaseTestCase
{
    use DatabaseTransactions;

    public function test_halaman_daftar_kategori_dapat_ditampilkan()
    {
        $response = $this->get('/cms/categories');
        $response->assertOk()->assertViewIs('categories.index');
    }

    public function test_index_mengembalikan_json_dalam_permintaan_ajax()
    {
        $response = $this->get('/cms/categories', [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ]);

        $response->assertOk()
                ->assertJsonStructure(['data']);
    }

    public function test_halaman_tambah_kategori_dapat_ditampilkan()
    {
        $response = $this->get('/cms/categories/create');
        $response->assertOk()->assertViewIs('categories.create');
    }

    public function test_dapat_menyimpan_kategori_baru()
    {
        $data = Category::factory()->make()->toArray();

        $response = $this->post('/cms/categories', $data);

        $response->assertRedirect('/cms/categories');
        $this->assertDatabaseHas('categories', ['name' => $data['name']]);
    }

    public function test_halaman_form_tambah_kategori_dapat_ditampilkan()
    {
        $category = Category::factory()->create();

        $response = $this->get("/cms/categories/{$category->id}/edit");

        $response->assertOk()->assertViewIs('categories.edit')->assertViewHas('category');
    }

    public function test_dapat_memperbarui_kategori_yang_ada()
    {
        $category = Category::factory()->create();
        $newData = ['name' => 'Kategori Baru', 'status' => 1];

        $response = $this->put("/cms/categories/{$category->id}", $newData);

        $response->assertRedirect('/cms/categories');
        $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Kategori Baru']);
    }

    public function test_gagal_update_kategori_yang_tidak_ada()
    {
        $nonExistentId = 999999;
        $data = ['name' => 'Invalid', 'status' => 1];

        $response = $this->from('/cms/categories')
                        ->put("/cms/categories/{$nonExistentId}", $data);

        $response->assertRedirect('/cms/categories');

        $response->assertSessionHas('error', 'Kategori tidak ditemukan');
    }

    public function test_hapus_kategori_tanpa_artikel()
    {
        $category = Category::factory()->create();

        $response = $this->delete("/cms/categories/{$category->id}");

        $response->assertRedirect('/cms/categories');
        $this->assertSoftDeleted('categories', ['id' => $category->id]);

    }

    public function test_delete_category_with_articles_should_fail()
    {
        $category = Category::factory()->create();
        Article::factory()->create(['category_id' => $category->id]);

        $response = $this->delete("/cms/categories/{$category->id}");

        $response->assertRedirect('/cms/categories');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
        $this->assertEquals('Masih ada artikel pada kategori tersebut', session('error'));
    }

    public function test_gagal_menghapus_kategori_yang_tidak_ditemukan()
    {
        $response = $this->delete('/cms/categories/999');

        $response->assertRedirect('/cms/categories');
        $this->assertEquals('Kategori tidak ditemukan', session('error'));
    }
}
