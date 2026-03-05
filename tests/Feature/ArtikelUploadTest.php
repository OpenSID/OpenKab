<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\BaseTestCase;

class ArtikelUploadTest extends BaseTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_upload_valid_image()
    {
        $file = UploadedFile::fake()->image('test-artikel.jpg', 800, 600);

        $response = $this->post(route('artikel.upload_gambar'), [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $response->assertJsonStructure([
            'success',
            'url',
            'path',
        ]);
    }

    /** @test */
    public function it_rejects_non_image_files()
    {
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->post(route('artikel.upload_gambar'), [
            'file' => $file,
        ]);

        // Should not return success
        $this->assertNotEquals(200, $response->status());
    }

    /** @test */
    public function it_rejects_files_larger_than_2mb()
    {
        $file = UploadedFile::fake()->image('large-image.jpg')->size(3000); // 3MB

        $response = $this->post(route('artikel.upload_gambar'), [
            'file' => $file,
        ]);

        // Should not return success
        $this->assertNotEquals(200, $response->status());
    }

    /** @test */
    public function it_accepts_jpg_format()
    {
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->post(route('artikel.upload_gambar'), [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
    }

    /** @test */
    public function it_accepts_jpeg_format()
    {
        $file = UploadedFile::fake()->image('test.jpeg');

        $response = $this->post(route('artikel.upload_gambar'), [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
    }

    /** @test */
    public function it_accepts_png_format()
    {
        $file = UploadedFile::fake()->image('test.png');

        $response = $this->post(route('artikel.upload_gambar'), [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment(['success' => true]);
    }

    /** @test */
    public function it_requires_file_parameter()
    {
        $response = $this->post(route('artikel.upload_gambar'), []);

        // Should return error (not success)
        $this->assertContains($response->status(), [302, 400, 500]);
    }

    /** @test */
    public function upload_returns_valid_url()
    {
        $file = UploadedFile::fake()->image('artikel-gambar.jpg');

        $response = $this->post(route('artikel.upload_gambar'), [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertArrayHasKey('url', $data);
        $this->assertIsString($data['url']);
        $this->assertNotEmpty($data['url']);
    }

    /** @test */
    public function upload_returns_valid_path()
    {
        $file = UploadedFile::fake()->image('artikel-gambar.jpg');

        $response = $this->post(route('artikel.upload_gambar'), [
            'file' => $file,
        ]);

        $response->assertStatus(200);
        $data = $response->json();

        $this->assertArrayHasKey('path', $data);
        $this->assertIsString($data['path']);
        $this->assertStringContainsString('uploads/artikel', $data['path']);
    }

    /** @test */
    public function upload_requires_authentication()
    {
        auth()->logout();
        
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->post(route('artikel.upload_gambar'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('login'));
    }
}
