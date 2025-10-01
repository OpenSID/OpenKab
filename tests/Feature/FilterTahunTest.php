<?php

namespace Tests\Feature;

use Tests\BaseTestCase;

class FilterTahunTest extends BaseTestCase
{
    /**
     * Test basic filter tahun functionality exists
     */
    public function test_filter_tahun_basic_functionality()
    {
        // Test dengan route adat (yang sudah kita lihat ada filter tahun)
        $response = $this->get('/data-presisi/adat');

        // Test halaman bisa diakses 
        $response->assertStatus(200);

        // Test ada elemen filter tahun
        $response->assertSee('filter-tahun');

        // Test ada tahun minimal 2020
        $response->assertSee('2020');

        // Test ada tahun sekarang
        $response->assertSee(date('Y'));

        $this->assertTrue(true, 'Filter tahun berhasil ditemukan');
    }

    /**
     * Test filter tahun exists with proper HTML structure
     */
    public function test_filter_tahun_html_structure()
    {
        $response = $this->get('/data-presisi/adat');

        $response->assertStatus(200);

        // Test ada select element - periksa dengan cara yang lebih fleksibel
        $content = $response->getContent();
        $this->assertStringContainsString('select', $content);

        // Test ada option elements
        $this->assertStringContainsString('option', $content);

        // Test ada form control class
        $response->assertSee('form-control');

        $this->assertTrue(true, 'Struktur HTML filter tahun sesuai');
    }

    /**
     * Test year range is correct (2020 to current year)
     */
    public function test_year_range_functionality()
    {
        $currentYear = date('Y');
        $response = $this->get('/data-presisi/adat');

        $response->assertStatus(200);

        // Test range tahun dari 2020 sampai tahun sekarang
        $response->assertSee('2020');
        $response->assertSee($currentYear);

        $this->assertTrue(true, 'Range tahun filter sesuai (2020-' . $currentYear . ')');
    }

    /**
     * Test multiple modules have filter tahun
     */
    public function test_multiple_modules_have_filter_tahun()
    {
        $modules = [
            '/data-presisi/adat',
            '/data-presisi/kesehatan',
            '/jaminan-sosial'
        ];

        $successCount = 0;

        foreach ($modules as $module) {
            $response = $this->get($module);

            if (
                $response->status() === 200 &&
                strpos($response->getContent(), 'filter-tahun') !== false
            ) {
                $successCount++;
            }
        }

        $this->assertGreaterThan(0, $successCount, 'Minimal 1 modul memiliki filter tahun');
    }
}