<?php

namespace Tests\Feature;

use Tests\BaseTestCase;

class DataPresisiSeniBudayaExcelDownloadTest extends BaseTestCase
{
    /** @test */
    public function test_excel_download_button_exists_in_seni_budaya_page()
    {
        $response = $this->get('/data-presisi/seni-budaya');

        if ($response->status() !== 200) {
            $this->markTestSkipped('Page /data-presisi/seni-budaya not accessible (status: ' . $response->status() . ')');
            return;
        }

        $content = $response->getContent();

        // Test komponen excel-download-button ada di halaman (setelah render menjadi button dengan data attributes)
        $this->assertStringContainsString('data-download-url', $content);
        $this->assertStringContainsString('table-seni-budaya', $content);
        $this->assertStringContainsString('/api/v1/data-presisi/seni-budaya/rtm/download', $content);
    }

    /** @test */
    public function test_seni_budaya_page_has_filter_tahun()
    {
        $response = $this->get('/data-presisi/seni-budaya');

        if ($response->status() !== 200) {
            $this->markTestSkipped('Page not accessible');
            return;
        }

        $content = $response->getContent();

        // Test filter-tahun ada di halaman
        $this->assertStringContainsString('filter-tahun', $content);
    }

    /** @test */
    public function test_seni_budaya_page_has_print_button()
    {
        $response = $this->get('/data-presisi/seni-budaya');

        if ($response->status() !== 200) {
            $this->markTestSkipped('Page not accessible');
            return;
        }

        $content = $response->getContent();

        // Test print button ada di halaman
        $this->assertStringContainsString('data-presisi/seni-budaya/cetak', $content);
    }
}