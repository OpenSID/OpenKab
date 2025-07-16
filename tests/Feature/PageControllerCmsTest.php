<?php

namespace Tests\Feature;

use App\Models\CMS\Page;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\BaseTestCase;

class PageControllerCmsTest extends BaseTestCase
{
    use DatabaseTransactions;

    /** @test */
    public function halaman_index_dapat_diakses()
    {
        $response = $this->get(route('pages.index'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.index');
    }

    /** @test */
    public function halaman_form_tambah_dapat_dibuka()
    {
        $response = $this->get(route('pages.create'));

        $response->assertStatus(200);
        $response->assertViewIs('pages.create');
    }

    /** @test */
    public function halaman_baru_dapat_disimpan()
    {
        Storage::fake('public');

        $data = [
            'title' => 'Judul Halaman',
            'slug' => 'judul-halaman',
            'published_at' => now()->format('d/m/Y'),
            'content' => 'Ini adalah konten halaman.',
            'state' => true, // HARUS boolean, bukan string "aktif"
            'foto' => UploadedFile::fake()->image('thumbnail.jpg'),
        ];

        $response = $this->post(route('pages.store'), $data);

        $response->assertRedirect(route('pages.index'));
        $this->assertDatabaseHas('pages', ['title' => 'Judul Halaman']);
    }

    /** @test */
    public function halaman_dapat_diedit()
    {
        $page = Page::factory()->create();

        $response = $this->get(route('pages.edit', $page->id));

        $response->assertStatus(200);
        $response->assertViewIs('pages.edit');
        $response->assertViewHas('page');
    }

    /** @test */
    public function halaman_dapat_diperbarui()
    {
        $page = Page::factory()->create([
            'title' => 'Lama',
        ]);

        $data = [
            'title' => 'Baru',
            'slug' => 'baru',
            'published_at' => now()->format('d/m/Y'),
            'content' => 'Konten halaman yang diperbarui.',
            'state' => true,
        ];

        $response = $this->put(route('pages.update', $page->id), $data);

        $response->assertRedirect(route('pages.index'));
        $this->assertDatabaseHas('pages', ['title' => 'Baru']);
    }

    /** @test */
    public function halaman_dapat_dihapus()
    {
        $page = Page::factory()->create();

        $response = $this->delete(route('pages.destroy', $page->id));

        $response->assertRedirect(route('pages.index'));
        $this->assertSoftDeleted('pages', ['id' => $page->id]);
    }
}
