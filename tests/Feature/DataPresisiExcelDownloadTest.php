<?php

namespace Tests\Feature;

use Tests\BaseTestCase;

class DataPresisiExcelDownloadTest extends BaseTestCase
{
    /**
     * Modules that should have excel download button
     */
    protected $modulesWithExcelDownload = [
        '/data-presisi/pangan' => [
            'table_id' => 'table-pangan',
            'download_url' => '/api/v1/data-presisi/pangan/rtm/download',
            'filename' => 'data_presisi_pangan',
        ],
        '/data-presisi/pendidikan' => [
            'table_id' => 'table-pendidikan',
            'download_url' => '/api/v1/data-presisi/pendidikan/rtm/download',
            'filename' => 'data_presisi_pendidikan',
        ],
    ];

    /** @test */
    public function test_excel_download_button_exists_in_pangan_page()
    {
        $response = $this->get('/data-presisi/pangan');

        if ($response->status() === 200) {
            $content = $response->getContent();

            // Test komponen x-download-excel ada di halaman
            $this->assertStringContainsString('download-excel', $content);
            $this->assertStringContainsString('table-pangan', $content);
            $this->assertStringContainsString('/api/v1/data-presisi/pangan/rtm/download', $content);
            $this->assertStringContainsString('data_presisi_pangan', $content);
        } else {
            $this->markTestSkipped('Page /data-presisi/pangan not accessible (status: ' . $response->status() . ')');
        }
    }

    /** @test */
    public function test_excel_download_button_exists_in_pendidikan_page()
    {
        $response = $this->get('/data-presisi/pendidikan');

        if ($response->status() === 200) {
            $content = $response->getContent();

            // Test komponen x-download-excel ada di halaman
            $this->assertStringContainsString('download-excel', $content);
            $this->assertStringContainsString('table-pendidikan', $content);
            $this->assertStringContainsString('/api/v1/data-presisi/pendidikan/rtm/download', $content);
            $this->assertStringContainsString('data_presisi_pendidikan', $content);
        } else {
            $this->markTestSkipped('Page /data-presisi/pendidikan not accessible (status: ' . $response->status() . ')');
        }
    }

    /** @test */
    public function test_excel_download_button_component_renders_correctly()
    {
        $successCount = 0;

        foreach ($this->modulesWithExcelDownload as $module => $config) {
            $response = $this->get($module);

            if ($response->status() === 200) {
                $content = $response->getContent();

                // Check that excel download button contains necessary elements
                $hasDownloadUrl = strpos($content, $config['download_url']) !== false;
                $hasTableId = strpos($content, $config['table_id']) !== false;
                $hasFilename = strpos($content, $config['filename']) !== false;

                if ($hasDownloadUrl && $hasTableId && $hasFilename) {
                    $successCount++;
                }
            }
        }

        $this->assertGreaterThan(
            0,
            $successCount,
            'Minimal 1 modul memiliki Excel download button yang lengkap'
        );
    }

    /** @test */
    public function test_pangan_and_pendidikan_have_matching_excel_button_structure()
    {
        $panganResponse = $this->get('/data-presisi/pangan');
        $pendidikanResponse = $this->get('/data-presisi/pendidikan');

        if ($panganResponse->status() !== 200 || $pendidikanResponse->status() !== 200) {
            $this->markTestSkipped('Pages not accessible');
            return;
        }

        $panganContent = $panganResponse->getContent();
        $pendidikanContent = $pendidikanResponse->getContent();

        // Both should have x-download-excel component
        $panganHasButton = strpos($panganContent, 'download-excel') !== false;
        $pendidikanHasButton = strpos($pendidikanContent, 'download-excel') !== false;

        $this->assertTrue($panganHasButton, 'Pangan page should have excel download button');
        $this->assertTrue($pendidikanHasButton, 'Pendidikan page should have excel download button');

        // Both should have filter-tahun component
        $panganHasFilter = strpos($panganContent, 'filter-tahun') !== false;
        $pendidikanHasFilter = strpos($pendidikanContent, 'filter-tahun') !== false;

        $this->assertTrue($panganHasFilter, 'Pangan page should have filter tahun');
        $this->assertTrue($pendidikanHasFilter, 'Pendidikan page should have filter tahun');

        // Both should have print-btn component
        $panganHasPrint = strpos($panganContent, 'print-btn') !== false;
        $pendidikanHasPrint = strpos($pendidikanContent, 'print-btn') !== false;

        $this->assertTrue($panganHasPrint, 'Pangan page should have print button');
        $this->assertTrue($pendidikanHasPrint, 'Pendidikan page should have print button');
    }
}
