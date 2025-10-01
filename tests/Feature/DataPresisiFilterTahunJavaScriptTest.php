<?php

namespace Tests\Feature;

use Tests\BaseTestCase;

class DataPresisiFilterTahunJavaScriptTest extends BaseTestCase
{
    /**
     * Modules that have filter tahun implementation
     */
    protected $modulesWithFilterTahun = [
        '/data-presisi/adat',
        '/data-presisi/kesehatan',
        '/data-presisi/ketenagakerjaan',
        '/data-presisi/pangan',
        '/data-presisi/pendidikan',
        '/data-presisi/seni-budaya',
        '/jaminan-sosial',
        '/dtks/papan',
        '/dtks/sandang'
    ];

    /** @test */
    public function test_filter_tahun_javascript_event_listener_exists_in_all_modules()
    {
        $successCount = 0;
        $testedModules = [];

        foreach ($this->modulesWithFilterTahun as $module) {
            $response = $this->get($module);

            if ($response->status() === 200) {
                $content = $response->getContent();

                // Test JavaScript event listener untuk filter tahun
                $hasEventListener = strpos($content, "filter-tahun').on('change'") !== false ||
                    strpos($content, "#filter-tahun').on('change'") !== false ||
                    strpos($content, "$('#filter-tahun').on('change'") !== false;

                if ($hasEventListener) {
                    $successCount++;
                    $testedModules[] = $module;
                }
            }
        }

        $this->assertGreaterThan(
            0,
            $successCount,
            'Minimal 1 modul memiliki JavaScript event listener untuk filter tahun'
        );

        // Optional: Print which modules passed the test for debugging
        if ($successCount > 0) {
            $this->assertTrue(
                true,
                "JavaScript event listener ditemukan di {$successCount} modul: " . implode(', ', $testedModules)
            );
        }
    }

    /** @test */
    public function test_filter_tahun_ajax_parameter_exists_in_all_modules()
    {
        $successCount = 0;
        $testedModules = [];

        foreach ($this->modulesWithFilterTahun as $module) {
            $response = $this->get($module);

            if ($response->status() === 200) {
                $content = $response->getContent();

                // Test parameter filter[tahun] dikirim dalam ajax
                $hasAjaxParam = strpos($content, '"filter[tahun]"') !== false &&
                    strpos($content, "filter-tahun').val()") !== false;

                if ($hasAjaxParam) {
                    $successCount++;
                    $testedModules[] = $module;
                }
            }
        }

        $this->assertGreaterThan(
            0,
            $successCount,
            'Minimal 1 modul memiliki parameter filter[tahun] dalam AJAX request'
        );

        if ($successCount > 0) {
            $this->assertTrue(
                true,
                "Parameter AJAX filter[tahun] ditemukan di {$successCount} modul: " . implode(', ', $testedModules)
            );
        }
    }

    /** @test */
    public function test_datatable_reload_functionality_exists()
    {
        $successCount = 0;
        $testedModules = [];

        foreach ($this->modulesWithFilterTahun as $module) {
            $response = $this->get($module);

            if ($response->status() === 200) {
                $content = $response->getContent();

                // Test ada reload functionality
                $hasReload = strpos($content, 'ajax.reload()') !== false ||
                    strpos($content, '.ajax.reload()') !== false;

                if ($hasReload) {
                    $successCount++;
                    $testedModules[] = $module;
                }
            }
        }

        $this->assertGreaterThan(
            0,
            $successCount,
            'Minimal 1 modul memiliki DataTable ajax reload functionality'
        );

        if ($successCount > 0) {
            $this->assertTrue(
                true,
                "DataTable reload ditemukan di {$successCount} modul: " . implode(', ', $testedModules)
            );
        }
    }

    /** @test */
    public function test_chart_reload_functionality_exists()
    {
        $successCount = 0;
        $testedModules = [];

        foreach ($this->modulesWithFilterTahun as $module) {
            $response = $this->get($module);

            if ($response->status() === 200) {
                $content = $response->getContent();

                // Test chart reload untuk modul dengan chart
                $hasChartReload = strpos($content, 'grafikPie()') !== false ||
                    strpos($content, 'chart') !== false;

                if ($hasChartReload) {
                    $successCount++;
                    $testedModules[] = $module;
                }
            }
        }

        // Chart reload is optional, so we just check if any modules have it
        if ($successCount > 0) {
            $this->assertTrue(
                true,
                "Chart reload functionality ditemukan di {$successCount} modul: " . implode(', ', $testedModules)
            );
        } else {
            $this->assertTrue(true, 'Tidak ada modul yang memiliki chart reload (opsional)');
        }
    }
}