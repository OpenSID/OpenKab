<?php

namespace Tests\Feature;

use App\Models\CMS\Download;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\BaseTestCase;

class DownloadControllerCmsTest extends BaseTestCase
{
    use DatabaseTransactions;

    /** @test */
    public function halaman_index_download_dapat_diakses()
    {
        $response = $this->get(route('downloads.index'));

        $response->assertStatus(200);
        $response->assertViewIs('downloads.index');
    }

    /** @test */
    public function form_tambah_download_dapat_diakses()
    {
        $response = $this->get(route('downloads.create'));

        $response->assertStatus(200);
        $response->assertViewIs('downloads.create');
    }

    /** @test */
    public function file_download_baru_dapat_disimpan()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('contoh.pdf', 100, 'application/pdf');
        $data = [
            'title' => 'File PDF',
            'state' => 1,
            'description' => 'Contoh file yang dapat diunduh.',
            'download_file' => $file,
        ];

        Storage::disk('public')->put('downloads', $file);

        $response = $this->post(route('downloads.store'), $data);

        $response->assertRedirect(route('downloads.index'));
        $this->assertDatabaseHas('downloads', ['title' => 'File PDF']);
    }

    /** @test */
    public function form_edit_download_dapat_diakses()
    {
        $download = Download::factory()->create();

        $response = $this->get(route('downloads.edit', $download->id));

        $response->assertStatus(200);
        $response->assertViewIs('downloads.edit');
    }

    /** @test */
    public function file_download_dapat_diperbarui()
    {
        $download = Download::factory()->create([
            'title' => 'Lama',
        ]);

        $data = [
            'title' => 'Baru',
            'description' => 'Contoh file yang dapat diunduh.',
            'state' => 1,
        ];

        $response = $this->put(route('downloads.update', $download->id), $data);

        $response->assertRedirect(route('downloads.index'));
        $this->assertDatabaseHas('downloads', ['title' => 'Baru']);
    }

    /** @test */
    public function file_download_dapat_dihapus()
    {
        $download = Download::factory()->create();

        $response = $this->delete(route('downloads.destroy', $download->id));

        $response->assertRedirect(route('downloads.index'));
        $this->assertSoftDeleted('downloads', ['id' => $download->id]);
    }
}
