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

    #[Test]
    public function halaman_index_download_dapat_diakses()
    {
        $response = $this->get(route('downloads.index'));

        $response->assertStatus(200);
        $response->assertViewIs('downloads.index');
    }

    #[Test]
    public function form_tambah_download_dapat_diakses()
    {
        $response = $this->get(route('downloads.create'));

        $response->assertStatus(200);
        $response->assertViewIs('downloads.create');
    }

    #[Test]
    public function file_download_baru_dapat_disimpan()
    {
        Storage::fake('public');
        
        // Create a simple valid PDF content
        $pdfContent = '%PDF-1.4
%����
1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj
2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj
3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]/Resources<<>>>>endobj
xref
0 4
0000000000 65535 f
0000000015 00000 n
0000000061 00000 n
0000000111 00000 n
trailer<</Size 4/Root 1 0 R>>
startxref
178
%%EOF';
        
        $tempFile = tempnam(sys_get_temp_dir(), 'test_pdf_');
        file_put_contents($tempFile, $pdfContent);
        
        $file = new UploadedFile(
            $tempFile,
            'contoh.pdf',
            'application/pdf',
            UPLOAD_ERR_OK,
            true
        );
        
        $data = [
            'title' => 'File PDF',
            'state' => true,
            'description' => 'Contoh file yang dapat diunduh.',
            'download_file' => $file,
        ];

        $response = $this->post(route('downloads.store'), $data);

        $response->assertRedirect(route('downloads.index'));
        $this->assertDatabaseHas('downloads', ['title' => 'File PDF']);
    }

    #[Test]
    public function form_edit_download_dapat_diakses()
    {
        $download = Download::factory()->create();

        $response = $this->get(route('downloads.edit', $download->id));

        $response->assertStatus(200);
        $response->assertViewIs('downloads.edit');
    }

    #[Test]
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

    #[Test]
    public function file_download_dapat_dihapus()
    {
        $download = Download::factory()->create();

        $response = $this->delete(route('downloads.destroy', $download->id));

        $response->assertRedirect(route('downloads.index'));
        $this->assertSoftDeleted('downloads', ['id' => $download->id]);
    }
}
