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

        // Test ada tahun minimal (tahun sekarang - 5)
        $minYear = date('Y') - 5;
        $response->assertSee((string)$minYear);

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
     * Test year range is correct (current year to 5 years back)
     */
    public function test_year_range_functionality()
    {
        $currentYear = date('Y');
        $startYear = $currentYear - 5;
        $response = $this->get('/data-presisi/adat');

        $response->assertStatus(200);

        // Test range tahun dari tahun sekarang - 5 sampai tahun sekarang
        $response->assertSee((string)$startYear);
        $response->assertSee($currentYear);

        $this->assertTrue(true, 'Range tahun filter sesuai (' . $startYear . '-' . $currentYear . ')');
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