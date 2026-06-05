<?php

namespace Tests\Feature;

use Tests\BaseTestCase;

class DataPresisiKetenagakerjaanControllerTest extends BaseTestCase
{
    // ==========================================
    // Index Tests
    // ==========================================

    #[Test]
    public function test_can_access_ketenagakerjaan_index_page(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.index'));

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.data_presisi.ketenagakerjaan.index');
    }

    #[Test]
    public function test_index_page_has_correct_title(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.index'));

        $response->assertViewHas('title', 'Data Presisi Ketenagakerjaan');
    }

    #[Test]
    public function test_index_page_contains_required_elements(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.index'));

        $content = $response->getContent();

        // Test DataTable exists
        $this->assertStringContainsString('id="table-ketenagakerjaan"', $content);

        // Test chart canvas exists
        $this->assertStringContainsString('id="barChart"', $content);

        // Test filter tahun exists
        $this->assertStringContainsString('filter-tahun', $content);
    }

    #[Test]
    public function test_index_page_has_table_headers(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.index'));

        $content = $response->getContent();

        $expectedHeaders = [
            'Aksi',
            'NIK',
            'Nama Kepala Keluarga',
            'Jumlah Anggota RTM',
            'Jenis Pekerjaan',
            'Tempat Kerja',
        ];

        foreach ($expectedHeaders as $header) {
            $this->assertStringContainsString($header, $content);
        }
    }

    #[Test]
    public function test_index_page_has_excel_download_button(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.index'));

        $content = $response->getContent();

        $this->assertStringContainsString('download-excel', $content);
        $this->assertStringContainsString('table-ketenagakerjaan', $content);
        $this->assertStringContainsString('/api/v1/data-presisi/ketenagakerjaan/rtm/download', $content);
        $this->assertStringContainsString('data_presisi_ketenagakerjaan', $content);
    }

    #[Test]
    public function test_index_page_has_print_button(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.index'));

        $content = $response->getContent();

        $this->assertStringContainsString('ketenagakerjaan/cetak', $content);
    }

    // ==========================================
    // Detail Tests
    // ==========================================

    #[Test]
    public function test_can_access_ketenagakerjaan_detail_page(): void
    {
        $data = json_encode([
            'rtm_id' => '1',
            'no_kartu_rumah' => '1234567890',
            'nama_kepala_keluarga' => 'John Doe',
            'alamat' => 'Jl. Test No. 1',
            'jumlah_anggota' => 4,
            'jumlah_kk' => 1,
        ]);

        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail', ['data' => $data]));

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.data_presisi.ketenagakerjaan.detail');
    }

    #[Test]
    public function test_detail_page_has_decoded_json_data(): void
    {
        $data = json_encode([
            'rtm_id' => '1',
            'no_kartu_rumah' => '1234567890',
            'nama_kepala_keluarga' => 'John Doe',
            'alamat' => 'Jl. Test No. 1',
            'jumlah_anggota' => 4,
            'jumlah_kk' => 1,
        ]);

        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail', ['data' => $data]));

        $response->assertViewHas('data');
        $viewData = $response->viewData('data');

        $this->assertEquals('1', $viewData->rtm_id);
        $this->assertEquals('1234567890', $viewData->no_kartu_rumah);
        $this->assertEquals('John Doe', $viewData->nama_kepala_keluarga);
        $this->assertEquals('Jl. Test No. 1', $viewData->alamat);
        $this->assertEquals(4, $viewData->jumlah_anggota);
        $this->assertEquals(1, $viewData->jumlah_kk);
    }

    #[Test]
    public function test_detail_page_displays_kepala_keluarga_name(): void
    {
        $data = json_encode([
            'rtm_id' => '1',
            'no_kartu_rumah' => '1234567890',
            'nama_kepala_keluarga' => 'Jane Doe',
            'alamat' => 'Jl. Test',
            'jumlah_anggota' => 3,
            'jumlah_kk' => 1,
        ]);

        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail', ['data' => $data]));

        $content = $response->getContent();

        $this->assertStringContainsString('Jane Doe', $content);
        $this->assertStringContainsString('1234567890', $content);
    }

    #[Test]
    public function test_detail_page_has_detail_table(): void
    {
        $data = json_encode([
            'rtm_id' => '1',
            'no_kartu_rumah' => '1234567890',
            'nama_kepala_keluarga' => 'John Doe',
            'alamat' => 'Jl. Test',
            'jumlah_anggota' => 3,
            'jumlah_kk' => 1,
        ]);

        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail', ['data' => $data]));

        $content = $response->getContent();

        $this->assertStringContainsString('id="detail-ketenagakerjaan"', $content);
    }

    #[Test]
    public function test_detail_page_has_back_link_to_index(): void
    {
        $data = json_encode([
            'rtm_id' => '1',
            'no_kartu_rumah' => '1234567890',
            'nama_kepala_keluarga' => 'John Doe',
            'alamat' => 'Jl. Test',
            'jumlah_anggota' => 3,
            'jumlah_kk' => 1,
        ]);

        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail', ['data' => $data]));

        $content = $response->getContent();

        $this->assertStringContainsString(route('data-pokok.data-presisi-ketenagakerjaan.index'), $content);
    }

    // ==========================================
    // Cetak Tests
    // ==========================================

    #[Test]
    public function test_can_access_ketenagakerjaan_cetak_page(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.cetak'));

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.data_presisi.ketenagakerjaan.cetak');
    }

    #[Test]
    public function test_cetak_page_has_filter_view_variable(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.cetak'));

        $response->assertViewHas('filter');
    }

    #[Test]
    public function test_cetak_page_passes_query_string_as_filter(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.cetak') . '?tahun=2024&status=active');

        $response->assertViewHas('filter');
        $filter = $response->viewData('filter');

        $this->assertStringContainsString('tahun=2024', $filter);
        $this->assertStringContainsString('status=active', $filter);
    }

    #[Test]
    public function test_cetak_page_has_table_with_correct_id(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.cetak'));

        $content = $response->getContent();

        $this->assertStringContainsString('id="tabel-ketenagakerjaan"', $content);
    }

    #[Test]
    public function test_cetak_page_has_correct_table_headers(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.cetak'));

        $content = $response->getContent();

        $expectedHeaders = [
            'NO',
            'NIK',
            'NAMA KEPALA KELUARGA',
            'JUMLAH ANGGOTA',
            'JENIS PEKERJAAN',
            'TEMPAT KERJA',
            'FREKWENSI MENGIKUTI PELATIHAN SETAHUN',
            'JENIS PELATIHAN DIIKUTI SETAHUN',
            'TANGGAL PENGISIAN',
            'STATUS PENGISIAN',
        ];

        foreach ($expectedHeaders as $header) {
            $this->assertStringContainsString($header, $content);
        }
    }

    #[Test]
    public function test_cetak_page_uses_cetak_layout(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.cetak'));

        $response->assertStatus(200);
        $content = $response->getContent();

        $this->assertStringContainsString('Cetak | Data Penduduk Data Presisi Ketenagakerjaan', $content);
    }

    // ==========================================
    // DetailData Tests
    // ==========================================

    #[Test]
    public function test_can_access_ketenagakerjaan_detail_data_page(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data'));

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.data_presisi.ketenagakerjaan.detail_data');
    }

    #[Test]
    public function test_detail_data_page_has_title_and_colomn(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data'));

        $response->assertViewHas('title');
        $response->assertViewHas('colomn');
    }

    #[Test]
    public function test_detail_data_default_title_without_judul(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data'));

        $title = $response->viewData('title');

        $this->assertEquals('Data Presisi Ketenagakerjaan - ', $title);
    }

    #[Test]
    public function test_detail_data_title_includes_judul(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data', ['judul' => 'Test Judul']));

        $title = $response->viewData('title');

        $this->assertStringContainsString('Test Judul', $title);
    }

    #[Test]
    public function test_detail_data_title_sanitizes_xss(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data', ['judul' => '<script>alert("xss")</script>']));

        $title = $response->viewData('title');

        $this->assertStringNotContainsString('<script>', $title);
        $this->assertStringNotContainsString('</script>', $title);
    }

    #[Test]
    public function test_detail_data_colomn_empty_without_filter(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data'));

        $colomn = $response->viewData('colomn');

        $this->assertEquals('', $colomn);
    }

    #[Test]
    public function test_detail_data_colomn_with_valid_filter(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data', [
            'filter' => [
                'tipe' => 'jenis_pekerjaan',
                'nilai' => 'petani',
            ],
        ]));

        $colomn = $response->viewData('colomn');

        $this->assertEquals('jenis_pekerjaan:petani', $colomn);
    }

    #[Test]
    public function test_detail_data_colomn_empty_when_tipe_missing(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data', [
            'filter' => [
                'nilai' => 'petani',
            ],
        ]));

        $colomn = $response->viewData('colomn');

        $this->assertEquals('', $colomn);
    }

    #[Test]
    public function test_detail_data_colomn_empty_when_nilai_missing(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data', [
            'filter' => [
                'tipe' => 'jenis_pekerjaan',
            ],
        ]));

        $colomn = $response->viewData('colomn');

        $this->assertEquals('', $colomn);
    }

    #[Test]
    public function test_detail_data_colomn_empty_when_tipe_empty_string(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data', [
            'filter' => [
                'tipe' => '',
                'nilai' => 'petani',
            ],
        ]));

        $colomn = $response->viewData('colomn');

        $this->assertEquals('', $colomn);
    }

    #[Test]
    public function test_detail_data_colomn_empty_when_nilai_empty_string(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data', [
            'filter' => [
                'tipe' => 'jenis_pekerjaan',
                'nilai' => '',
            ],
        ]));

        $colomn = $response->viewData('colomn');

        $this->assertEquals('', $colomn);
    }

    #[Test]
    public function test_detail_data_page_has_detail_table(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data'));

        $content = $response->getContent();

        $this->assertStringContainsString('id="detail-ketenagakerjaan"', $content);
    }

    #[Test]
    public function test_detail_data_page_has_filter_tahun_component(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data'));

        $content = $response->getContent();

        $this->assertStringContainsString('filter-tahun', $content);
    }

    #[Test]
    public function test_detail_data_page_has_correct_table_headers(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data'));

        $content = $response->getContent();

        $expectedHeaders = [
            'NO',
            'NIK',
            'NOMOR KK',
            'NAMA',
            'PEKERJAAN',
            'TEMPAT KERJA',
            'FREKWENSI MENGIKUTI PELATIHAN SETAHUN',
            'JENIS PELATIHAN YANG DIIKUTI SETAHUN',
            'TANGGAL PENGISIAN',
            'STATUS PENGISIAN',
        ];

        foreach ($expectedHeaders as $header) {
            $this->assertStringContainsString($header, $content);
        }
    }

    #[Test]
    public function test_detail_data_validation_requires_filter_tipe_when_filter_nilai_present(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data', [
            'filter' => [
                'nilai' => 'petani',
            ],
        ]));

        $response->assertStatus(200);
        $colomn = $response->viewData('colomn');

        $this->assertEquals('', $colomn);
    }

    #[Test]
    public function test_detail_data_validation_requires_filter_nilai_when_filter_tipe_present(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data', [
            'filter' => [
                'tipe' => 'jenis_pekerjaan',
            ],
        ]));

        $response->assertStatus(200);
        $colomn = $response->viewData('colomn');

        $this->assertEquals('', $colomn);
    }

    #[Test]
    public function test_detail_data_title_strips_html_tags(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data', ['judul' => '<b>Bold</b> <i>Italic</i>']));

        $title = $response->viewData('title');

        $this->assertStringNotContainsString('<b>', $title);
        $this->assertStringNotContainsString('<i>', $title);
        $this->assertStringContainsString('Bold', $title);
        $this->assertStringContainsString('Italic', $title);
    }

    #[Test]
    public function test_detail_data_title_encodes_special_characters(): void
    {
        $response = $this->get(route('data-pokok.data-presisi-ketenagakerjaan.detail_data', ['judul' => 'Test "Quotes" & <Tags>']));

        $title = $response->viewData('title');

        $this->assertStringNotContainsString('<Tags>', $title);
        $this->assertStringContainsString('&', $title);
    }
}
