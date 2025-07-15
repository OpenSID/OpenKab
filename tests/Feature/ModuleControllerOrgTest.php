<?php

namespace Tests\Feature;

use Tests\WebsiteTestCase;

class ModuleControllerOrgTest extends WebsiteTestCase
{
    /** @test */
    public function it_can_access_org_module_page()
    {
        // Act: Akses halaman bagan organisasi
        $response = $this->get('/module/org');

        // Assert: Memastikan halaman dapat diakses tanpa error
        $response->assertStatus(200);
        $response->assertViewIs('web.module');
        $response->assertViewHas('moduleName', 'org');
        $response->assertViewHas('content');
    }

    /** @test */
    public function it_shows_org_chart_elements_in_view()
    {
        // Act: Akses halaman
        $response = $this->get('/module/org');

        // Assert: Memastikan elemen-elemen penting ada di halaman
        $response->assertStatus(200);
        $response->assertSee('Bagan Organisasi');
        $response->assertSee('chart-container');
    }

    /** @test */
    public function it_loads_required_assets_for_org_chart()
    {
        // Act: Akses halaman
        $response = $this->get('/module/org');

        // Assert: Memastikan asset orgchart dimuat
        $response->assertStatus(200);
        $response->assertSee('vendor/orgchart/jquery.orgchart.css');
        $response->assertSee('vendor/orgchart/jquery.orgchart.js');
        $response->assertSee('vendor/orgchart/html2canvas.min.js');
    }

    /** @test */
    public function it_returns_org_module_with_correct_data_structure()
    {
        // Act: Akses halaman
        $response = $this->get('/module/org');

        // Assert: Struktur response yang benar
        $response->assertStatus(200);
        
        // Memastikan view data tersedia
        $viewData = $response->getOriginalContent()->getData();
        $this->assertArrayHasKey('content', $viewData);
        $this->assertArrayHasKey('moduleName', $viewData);
        $this->assertEquals('org', $viewData['moduleName']);
        
        // Content harus berupa collection atau array (bisa kosong)
        $this->assertTrue(is_iterable($viewData['content']));
    }

    /** @test */
    public function it_includes_org_partial_view()
    {
        // Act: Akses halaman
        $response = $this->get('/module/org');

        // Assert: Memastikan menggunakan partial org yang benar
        $response->assertStatus(200);
        $response->assertViewIs('web.module');
        
        // Memastikan JavaScript orgchart dimuat
        $response->assertSee('orgchart');
        $response->assertSee('nodeTemplate');
    }
}
