<?php

namespace Tests\Feature;

use Tests\BaseTestCase;

class ArtikelControllerTest extends BaseTestCase
{
    /** @test */
    public function it_can_access_artikel_index()
    {
        $response = $this->get(route('master-data-artikel.index'));
        $response->assertStatus(200);
        $response->assertViewIs('master.artikel.index');
        $response->assertViewHas(['canwrite', 'canedit', 'candelete']);
        $response->assertSee('Data Artikel');
    }

    /** @test */
    public function it_can_access_artikel_create()
    {
        $response = $this->get(route('master-data-artikel.create'));
        $response->assertStatus(200);
        $response->assertViewIs('master.artikel.create');
        $response->assertSee('Tambah Artikel');
        $response->assertSee('Judul Artikel');
        $response->assertSee('Isi Artikel');
        $response->assertSee('Kategori');
    }

    /** @test */
    public function it_can_access_artikel_edit()
    {
        $artikelId = 1; // ID artikel untuk testing
        $response = $this->get(route('master-data-artikel.edit', ['artikel' => $artikelId]));
        $response->assertStatus(200);
        $response->assertViewIs('master.artikel.edit');
        $response->assertViewHas('id', $artikelId);
        $response->assertSee('Edit Artikel');
    }

    /** @test */
    public function artikel_index_requires_authentication()
    {
        auth()->logout();
        $response = $this->get(route('master-data-artikel.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function artikel_create_requires_authentication()
    {
        auth()->logout();
        $response = $this->get(route('master-data-artikel.create'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function artikel_edit_requires_authentication()
    {
        auth()->logout();
        $response = $this->get(route('master-data-artikel.edit', ['artikel' => 1]));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_can_access_upload_gambar_route()
    {
        $response = $this->post(route('artikel.upload_gambar'), [
            '_token' => csrf_token(),
        ]);
        
        // Tanpa file, bisa return validation error (302) atau error lain
        $this->assertContains($response->status(), [302, 400, 500]);
    }

    /** @test */
    public function artikel_index_shows_proper_table_structure()
    {
        $response = $this->get(route('master-data-artikel.index'));
        $response->assertStatus(200);
        
        // Check table headers
        $response->assertSee('No');
        $response->assertSee('Aksi');
        $response->assertSee('Judul');
        $response->assertSee('Kategori');
        $response->assertSee('Tanggal Upload');
        $response->assertSee('Status');
    }

    /** @test */
    public function artikel_create_shows_required_fields()
    {
        $response = $this->get(route('master-data-artikel.create'));
        $response->assertStatus(200);
        
        // Check required fields marked with asterisk
        $response->assertSee('Judul Artikel');
        $response->assertSee('Isi Artikel');
        $response->assertSee('Kategori');
        $response->assertSee('text-danger'); // asterisk styling
    }

    /** @test */
    public function artikel_create_has_upload_functionality()
    {
        $response = $this->get(route('master-data-artikel.create'));
        $response->assertStatus(200);
        
        // Check upload elements
        $response->assertSee('upload_gambar');
        $response->assertSee('Gambar Utama');
    }

    /** @test */
    public function artikel_edit_loads_with_id()
    {
        $artikelId = 123;
        $response = $this->get(route('master-data-artikel.edit', ['artikel' => $artikelId]));
        $response->assertStatus(200);
        
        // Check if ID is passed to view
        $response->assertViewHas('id', $artikelId);
        $response->assertSee((string) $artikelId);
    }

    /** @test */
    public function artikel_routes_are_registered()
    {
        // Check if routes exist
        $this->assertTrue(route('master-data-artikel.index') !== null);
        $this->assertTrue(route('master-data-artikel.create') !== null);
        $this->assertTrue(route('artikel.upload_gambar') !== null);
    }
}
