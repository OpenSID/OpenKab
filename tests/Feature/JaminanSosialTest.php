<?php

namespace Tests\Feature;

use Tests\BaseTestCase;

class JaminanSosialTest extends BaseTestCase
{
    #[Test]
    public function test_can_access_jaminan_sosial_page()
    {
        $response = $this->get(route('jaminan-sosial'));

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.jaminan_sosial.index');
        $response->assertSee('Data Kepesertaan Program dan Statistik');
    }

    #[Test]
    public function test_jaminan_sosial_page_has_required_elements()
    {
        $response = $this->get(route('jaminan-sosial'));
        $content = $response->getContent();

        // Test DataTable exists
        $this->assertStringContainsString('id="jaminanSosial"', $content, 'DataTable jaminanSosial tidak ditemukan');

        // Test filter tahun exists
        $this->assertStringContainsString('filter-tahun', $content, 'Filter tahun tidak ditemukan');

        // Test charts exist
        $this->assertStringContainsString('id="pie1"', $content, 'Chart pie1 tidak ditemukan');
        $this->assertStringContainsString('id="pie2"', $content, 'Chart pie2 tidak ditemukan');
        $this->assertStringContainsString('id="pie4"', $content, 'Chart pie4 tidak ditemukan');
        
        // Test print button exists
        $this->assertStringContainsString('btn btn-primary btn-sm', $content, 'Print button tidak ditemukan');
        $this->assertStringContainsString('id="print-btn-jaminanSosial"', $content, 'Print button ID tidak ditemukan');
        
        // Test excel download button exists
        $this->assertStringContainsString('btn btn-success btn-sm', $content, 'Excel download button tidak ditemukan');
        $this->assertStringContainsString('id="download-excel"', $content, 'Excel download button ID tidak ditemukan');
    }

    #[Test]
    public function test_jaminan_sosial_has_correct_table_columns()
    {
        $response = $this->get(route('jaminan-sosial'));
        $content = $response->getContent();

        // Test table headers exist
        $expectedColumns = [
            'Aksi',
            'NIK',
            'Nama Kepala Keluarga',
            'Jumlah Anggota RTM',
            'Jenis Bantuan Sosial<br> Yang Pernah Diterima',
            'Jenis Gangguan Mental<br> Yang Diderita',
            'Jenis Penanganan <br>Penderita Gangguan Mental',
        ];

        foreach ($expectedColumns as $column) {
            $this->assertStringContainsString($column, $content, "Kolom '{$column}' tidak ditemukan");
        }
    }

    #[Test]
    public function test_jaminan_sosial_has_print_button()
    {
        $response = $this->get(route('jaminan-sosial'));        
        $content = $response->getContent();

        // Test print button rendered HTML exists (component is rendered to actual button)
        $this->assertStringContainsString('fa fa-print', $content, 'Icon print tidak ditemukan');
        $this->assertStringContainsString('data-print-url=', $content, 'Route print tidak ditemukan');
    }

    #[Test]
    public function test_jaminan_sosial_has_excel_download_button()
    {
        $response = $this->get(route('jaminan-sosial'));
        $content = $response->getContent();

        // Test excel download button rendered HTML exists (component is rendered to actual button)
        $this->assertStringContainsString('fa fa-file-excel', $content, 'Icon excel tidak ditemukan');
        $this->assertStringContainsString('data-download-url=', $content, 'Download URL tidak ditemukan');
    }

    #[Test]
    public function test_jaminan_sosial_has_datatable_configuration()
    {
        $response = $this->get(route('jaminan-sosial'));
        $content = $response->getContent();

        // Test DataTable configuration
        $this->assertStringContainsString('processing: true', $content, 'DataTable processing config tidak ditemukan');
        $this->assertStringContainsString('serverSide: true', $content, 'DataTable serverSide config tidak ditemukan');
        $this->assertStringContainsString('ordering: false', $content, 'DataTable ordering config tidak ditemukan');

        // Test API endpoint
        $this->assertStringContainsString('/api/v1/data-presisi/jaminan-sosial', $content, 'API endpoint tidak ditemukan');
    }

    #[Test]
    public function test_jaminan_sosial_has_filter_tahun_functionality()
    {
        $response = $this->get(route('jaminan-sosial'));
        $content = $response->getContent();

        // Test filter tahun change event listener exists
        $this->assertStringContainsString("$('#filter-tahun, #filter-status-kelengkapan').on('change'", $content, 'Event listener filter tahun tidak ditemukan');
        $this->assertStringContainsString('jaminanSosial.ajax.reload()', $content, 'DataTable reload pada filter tahun tidak ditemukan');
        $this->assertStringContainsString('grafikPie()', $content, 'Grafik reload pada filter tahun tidak ditemukan');
    }

    #[Test]
    public function test_jaminan_sosial_has_detail_control_functionality()
    {
        $response = $this->get(route('jaminan-sosial'));
        $content = $response->getContent();

        // Test detail control for expandable rows
        $this->assertStringContainsString('details-control', $content, 'Detail control class tidak ditemukan');
        $this->assertStringContainsString("jaminanSosial.on('click', 'td.details-control'", $content, 'Event listener detail control tidak ditemukan');
        $this->assertStringContainsString('row.child.isShown()', $content, 'Logika expand/collapse detail control tidak ditemukan');
    }

    #[Test]
    public function test_jaminan_sosial_detail_button_has_correct_route()
    {
        $response = $this->get(route('jaminan-sosial'));
        $content = $response->getContent();

        // Test detail button route exists (rendered as actual URL)
        $this->assertStringContainsString('jaminan-sosial/detail', $content, 'Route detail tidak ditemukan');
        $this->assertStringContainsString('?data=__DATA__', $content, 'Parameter data pada route detail tidak ditemukan');
    }

    #[Test]
    public function test_jaminan_sosial_uses_correct_api_filters()
    {
        $response = $this->get(route('jaminan-sosial'));
        $content = $response->getContent();

        // Test filter parameters in DataTable
        $this->assertStringContainsString('"page[size]"', $content, 'Filter page[size] tidak ditemukan');
        $this->assertStringContainsString('"page[number]"', $content, 'Filter page[number] tidak ditemukan');        
        $this->assertStringContainsString('"filter[search]"', $content, 'Filter search tidak ditemukan');
    }
}
