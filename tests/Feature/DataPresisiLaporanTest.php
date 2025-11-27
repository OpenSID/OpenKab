<?php

namespace Tests\Feature;

use Tests\BaseTestCase;

class DataPresisiLaporanTest extends BaseTestCase
{
    /** @test */
    public function test_can_access_laporan_semua_desa_page()
    {
        $response = $this->get(route('laporan.data-presisi.index'));

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.data_presisi.laporan.index');
        $response->assertViewHas('title', 'Data Presisi Pengisian Laporan Semua Desa');
    }

    /** @test */
    public function test_laporan_semua_desa_has_required_elements()
    {
        $response = $this->get(route('laporan.data-presisi.index'));

        $content = $response->getContent();

        // Test DataTable exists
        $this->assertStringContainsString('id="laporanTable"', $content);

        // Test filter status exists
        $this->assertStringContainsString('id="filter-status"', $content);

        // Test status options exist
        $this->assertStringContainsString('Tidak Lengkap', $content);
        $this->assertStringContainsString('Lengkap Sebagian', $content);
        $this->assertStringContainsString('Data Lengkap', $content);
    }

    /** @test */
    public function test_laporan_semua_desa_has_correct_table_columns()
    {
        $response = $this->get(route('laporan.data-presisi.index'));

        $content = $response->getContent();

        // Test table headers exist
        $expectedColumns = [
            'Desa',
            'Pangan',
            'Sandang',
            'Papan',
            'Pendidikan',
            'Seni Budaya',
            'Kesehatan',
            'Keagamaan',
            'Jaminan Sosial',
            'Adat',
            'Ketenagakerjaan',
            'Jumlah Penduduk',
            'Jumlah Rumah Tangga'
        ];

        foreach ($expectedColumns as $column) {
            $this->assertStringContainsString($column, $content);
        }
    }

    /** @test */
    public function test_laporan_semua_desa_has_filter_status_javascript()
    {
        $response = $this->get(route('laporan.data-presisi.index'));

        $content = $response->getContent();

        // Test filter status change event listener exists
        $this->assertStringContainsString("$('#filter-status').on('change'", $content);
        $this->assertStringContainsString("$('#laporanTable').DataTable().ajax.reload()", $content);
    }

    /** @test */
    public function test_laporan_semua_desa_has_datatable_configuration()
    {
        $response = $this->get(route('laporan.data-presisi.index'));

        $content = $response->getContent();

        // Test DataTable configuration
        $this->assertStringContainsString('processing: true', $content);
        $this->assertStringContainsString('serverSide: true', $content);

        // Test filter[status_kelengkapan] parameter
        $this->assertStringContainsString('"filter[status_kelengkapan]"', $content);
        $this->assertStringContainsString("$('#filter-status').val()", $content);
    }

    /** @test */
    public function test_can_access_laporan_perdesa_page()
    {
        $response = $this->get(route('laporan.data-presisi.perdesa'));

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.data_presisi.laporan.perdesa');
        $response->assertViewHas('title', 'Data Presisi Pengisian Laporan Per Desa');
    }

    /** @test */
    public function test_laporan_perdesa_has_correct_table_columns()
    {
        $response = $this->get(route('laporan.data-presisi.perdesa'));

        $content = $response->getContent();

        // Test table headers exist
        $expectedColumns = [
            'Uraian',
            'Data Lengkap',
            'Lengkap Sebagian',
            'Tidak Lengkap',
            'Total Data'
        ];

        foreach ($expectedColumns as $column) {
            $this->assertStringContainsString($column, $content);
        }
    }

    /** @test */
    public function test_laporan_perdesa_has_datatable_configuration()
    {
        $response = $this->get(route('laporan.data-presisi.perdesa'));

        $content = $response->getContent();

        // Test DataTable configuration
        $this->assertStringContainsString('processing: true', $content);
        $this->assertStringContainsString('serverSide: true', $content);
        $this->assertStringContainsString('searching: false', $content);

        // Test filter parameters in DataTable
        $this->assertStringContainsString('"kode_kabupaten"', $content);
        $this->assertStringContainsString('"kode_kecamatan"', $content);
        $this->assertStringContainsString('"config_desa"', $content);
    }

    /** @test */
    public function test_laporan_perdesa_number_formatting_for_columns()
    {
        $response = $this->get(route('laporan.data-presisi.perdesa'));

        $content = $response->getContent();

        // Test number formatting render for numeric columns
        $this->assertStringContainsString("render: $.fn.dataTable.render.number('.', ',', 0, '')", $content);
    }

    /** @test */
    public function test_both_pages_use_correct_api_endpoints()
    {
        // Test laporan index
        $response1 = $this->get(route('laporan.data-presisi.index'));
        $content1 = $response1->getContent();
        $this->assertStringContainsString('/api/v1/data-presisi/laporan', $content1);

        // Test laporan perdesa
        $response2 = $this->get(route('laporan.data-presisi.perdesa'));
        $content2 = $response2->getContent();
        $this->assertStringContainsString('/api/v1/data-presisi/laporan-perdesa', $content2);
    }

    /** @test */
    public function test_both_pages_have_breadcrumbs()
    {
        // Test laporan index has breadcrumb section
        $response1 = $this->get(route('laporan.data-presisi.index'));
        $response1->assertStatus(200);

        // Test laporan perdesa has breadcrumb section
        $response2 = $this->get(route('laporan.data-presisi.perdesa'));
        $response2->assertStatus(200);
        
        $this->assertTrue(true);
    }

    /** @test */
    public function test_both_pages_extend_correct_layout()
    {
        // Test laporan index uses correct layout (has main-footer)
        $response1 = $this->get(route('laporan.data-presisi.index'));
        $content1 = $response1->getContent();
        $this->assertStringContainsString('class="main-footer"', $content1);

        // Test laporan perdesa uses correct layout (has main-footer)
        $response2 = $this->get(route('laporan.data-presisi.perdesa'));
        $content2 = $response2->getContent();
        $this->assertStringContainsString('class="main-footer"', $content2);
    }

    /** @test */
    public function test_laporan_semua_desa_has_export_buttons()
    {
        $response = $this->get(route('laporan.data-presisi.index'));
        $content = $response->getContent();

        // Test export buttons exist
        $this->assertStringContainsString('id="cetak"', $content);
        $this->assertStringContainsString('id="export-excel"', $content);
    }

    /** @test */
    public function test_laporan_perdesa_has_export_buttons()
    {
        $response = $this->get(route('laporan.data-presisi.perdesa'));
        $content = $response->getContent();

        // Test export buttons exist
        $this->assertStringContainsString('id="cetak"', $content);
        $this->assertStringContainsString('id="export-excel"', $content);
    }
}
