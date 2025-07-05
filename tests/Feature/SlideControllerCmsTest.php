<?php

namespace Tests\Feature;

use App\Models\CMS\Slide;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\BaseTestCase;

class SlideControllerCmsTest extends BaseTestCase
{
    use DatabaseTransactions;

    /** @test */
    public function halaman_index_slide_dapat_diakses()
    {
        $response = $this->get(route('slides.index'));
        $response->assertStatus(200);
        $response->assertViewIs('slides.index');
    }

    /** @test */
    public function form_tambah_slide_dapat_diakses()
    {
        $response = $this->get(route('slides.create'));
        $response->assertStatus(200);
        $response->assertViewIs('slides.create');
    }

    /** @test */
    public function slide_baru_dapat_disimpan()
    {
        Storage::fake('public');

        $data = [
            'title' => 'Slide Baru',
            'description' => 'Deskripsi slide',
            'state' => 1,
            'foto' => UploadedFile::fake()->image('slide.jpg'),
        ];

        $response = $this->post(route('slides.store'), $data);

        $response->assertRedirect(route('slides.index'));
        $this->assertDatabaseHas('slides', ['title' => 'Slide Baru']);
    }

    /** @test */
    public function form_edit_slide_dapat_diakses()
    {
        $slide = Slide::factory()->create();

        $response = $this->get(route('slides.edit', $slide->id));

        $response->assertStatus(200);
        $response->assertViewIs('slides.edit');
        $response->assertViewHas('slide');
    }

    /** @test */
    public function slide_dapat_diperbarui()
    {
        $slide = Slide::factory()->create([
            'title' => 'Lama',
        ]);

        $data = [
            'title' => 'Slide Diperbarui',
            'description' => 'Deskripsi baru',
            'state' => 1,
        ];

        $response = $this->put(route('slides.update', $slide->id), $data);

        $response->assertRedirect(route('slides.index'));
        $this->assertDatabaseHas('slides', ['title' => 'Slide Diperbarui']);
    }

    /** @test */
    public function slide_dapat_dihapus()
    {
        $slide = Slide::factory()->create();

        $response = $this->delete(route('slides.destroy', $slide->id));

        $response->assertRedirect(route('slides.index'));
        $this->assertSoftDeleted('slides', ['id' => $slide->id]);
    }
}
