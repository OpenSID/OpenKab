<?php

namespace Tests\Feature;

use App\Models\CMS\Download;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\BaseTestCase;

class DownloadControllerCmsTest extends BaseTestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

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
        // Create a simple text file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_file_');
        file_put_contents($tempFile, 'This is a test file content for download.');

        $file = new UploadedFile(
            $tempFile,
            'document.txt',
            'text/plain',
            UPLOAD_ERR_OK,
            true
        );

        $data = [
            'title' => 'File Document',
            'state' => 1,
            'description' => 'Contoh file yang dapat diunduh.',
            'download_file' => $file,
        ];

        $response = $this->post(route('downloads.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('downloads', ['title' => 'File Document']);
        
        // Clean up
        unlink($tempFile);
    }

    /** @test */
    public function file_download_baru_ditolak_jika_berisi_kode_php()
    {
        // Create a file with PHP code (should be rejected)
        $tempFile = tempnam(sys_get_temp_dir(), 'malicious_');
        file_put_contents($tempFile, '<?php phpinfo(); ?>');

        $file = new UploadedFile(
            $tempFile,
            'shell.txt',
            'text/plain',
            UPLOAD_ERR_OK,
            true
        );

        $data = [
            'title' => 'Malicious File',
            'state' => true,
            'description' => 'File dengan kode PHP.',
            'download_file' => $file,
        ];

        $response = $this->post(route('downloads.store'), $data);

        // Should redirect back with error (validation or service rejection)
        $response->assertRedirect();
        $response->assertSessionHasErrors(['download_file']);
        
        // Clean up
        unlink($tempFile);
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

        $response->assertRedirect();
        $this->assertDatabaseHas('downloads', ['title' => 'Baru']);
    }

    /** @test */
    public function file_download_dengan_file_baru_dapat_diperbarui()
    {
        $download = Download::factory()->create([
            'title' => 'Lama',
        ]);

        // Create a simple text file
        $tempFile = tempnam(sys_get_temp_dir(), 'test_update_');
        file_put_contents($tempFile, 'Updated file content.');

        $file = new UploadedFile(
            $tempFile,
            'updated.txt',
            'text/plain',
            UPLOAD_ERR_OK,
            true
        );

        $data = [
            'title' => 'Baru',
            'description' => 'File dengan update baru.',
            'state' => 1,
            'download_file' => $file,
        ];

        $response = $this->put(route('downloads.update', $download->id), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('downloads', ['title' => 'Baru']);
        
        // Clean up
        unlink($tempFile);
    }

    /** @test */
    public function file_download_dapat_dihapus()
    {
        $download = Download::factory()->create();

        $response = $this->delete(route('downloads.destroy', $download->id));

        $response->assertRedirect();
        $this->assertSoftDeleted('downloads', ['id' => $download->id]);
    }
}
