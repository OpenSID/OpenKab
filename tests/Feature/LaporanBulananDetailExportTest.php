<?php

namespace Tests\Feature;

use Tests\BaseTestCase;

class LaporanBulananDetailExportTest extends BaseTestCase
{
    /** @test */
    public function test_detail_page_has_export_excel_button()
    {
        // Set up session data yang diperlukan
        session([
            'bulanku' => 1,
            'tahunku' => 2026,
            'kode_kabupaten' => '35',
        ]);

        $response = $this->get(route('laporan-bulanan.detail-penduduk', [
            'rincian' => 'awal',
            'tipe' => 'wni_l',
        ]));

        $response->assertStatus(200);
        $content = $response->getContent();

        // Verifikasi tombol Export Excel ada dengan icon dan class yang benar
        $this->assertStringContainsString('fa-file-excel', $content, 'Icon Excel tidak ditemukan');
        $this->assertStringContainsString('Export Excel', $content, 'Label Export Excel tidak ditemukan');
        $this->assertStringContainsString('btn-success', $content, 'Class btn-success tidak ditemukan');

        // Verifikasi tombol lama (Cetak) sudah tidak ada
        $this->assertStringNotContainsString('fa-print', $content, 'Icon print seharusnya sudah dihapus');
        $this->assertStringNotContainsString('>Cetak<', $content, 'Label Cetak seharusnya sudah dihapus');
    }

    /** @test */
    public function test_detail_page_export_excel_button_has_correct_route()
    {
        session([
            'bulanku' => 1,
            'tahunku' => 2026,
            'kode_kabupaten' => '35',
        ]);

        $response = $this->get(route('laporan-bulanan.detail-penduduk', [
            'rincian' => 'lahir',
            'tipe' => 'wni_p',
        ]));

        $response->assertStatus(200);
        $content = $response->getContent();

        // Verifikasi URL export ada dengan parameter yang benar
        $expectedRoutePattern = 'laporan-bulanan/export-excel-detail/lahir/wni_p';
        $this->assertStringContainsString($expectedRoutePattern, $content, 'Route export excel detail tidak ditemukan');
    }
}
