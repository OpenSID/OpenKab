<?php

namespace Tests\Unit;

use Tests\BaseTestCase;
use Illuminate\View\View;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class DataPresisiPanganDetailViewTest extends BaseTestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_renders_longitude_latitude_columns_in_datatable_header()
    {
        // Mock data untuk testing view
        $mockData = (object) [
            'rtm_id' => '12345',
            'no_kartu_rumah' => 'KRT001',
            'nama_kepala_keluarga' => 'Budi Santoso',
            'alamat' => 'Jl. Contoh No. 123',
            'jumlah_anggota' => 4,
            'jumlah_kk' => 1,
        ];

        // Render view dengan mock data
        $view = ViewFacade::make('data_pokok.data_presisi.pangan.detail', ['data' => $mockData]);
        $html = $view->render();

        // Assert bahwa header table mengandung LONGITUDE dan LATITUDE
        $this->assertStringContainsString('<th>LONGITUDE</th>', $html);
        $this->assertStringContainsString('<th>LATITUDE</th>', $html);
    }

    /** @test */
    public function it_configures_datatable_columns_for_longitude_latitude()
    {
        // Mock data untuk testing view
        $mockData = (object) [
            'rtm_id' => '12345',
            'no_kartu_rumah' => 'KRT001',
            'nama_kepala_keluarga' => 'Budi Santoso',
            'alamat' => 'Jl. Contoh No. 123',
            'jumlah_anggota' => 4,
            'jumlah_kk' => 1,
        ];

        // Render view dengan mock data
        $view = ViewFacade::make('data_pokok.data_presisi.pangan.detail', ['data' => $mockData]);
        $html = $view->render();

        // Assert bahwa JavaScript DataTable configuration mengandung mapping untuk longitude dan latitude
        $this->assertStringContainsString("{ data: 'attributes.longitude', orderable: false }", $html);
        $this->assertStringContainsString("{ data: 'attributes.latitude', orderable: false }", $html);
        
        // Assert bahwa koordinat dikonfigurasi sebagai kolom yang tidak dapat diurutkan
        $this->assertStringContainsString("orderable: false", $html);
    }

    /** @test */
    public function it_includes_longitude_latitude_in_correct_column_order()
    {
        // Mock data untuk testing view
        $mockData = (object) [
            'rtm_id' => '12345',
            'no_kartu_rumah' => 'KRT001',
            'nama_kepala_keluarga' => 'Budi Santoso',
            'alamat' => 'Jl. Contoh No. 123',
            'jumlah_anggota' => 4,
            'jumlah_kk' => 1,
        ];

        // Render view dengan mock data
        $view = ViewFacade::make('data_pokok.data_presisi.pangan.detail', ['data' => $mockData]);
        $html = $view->render();

        // Assert urutan kolom dengan memeriksa posisi longitude dan latitude
        // Longitude harus muncul sebelum latitude dalam urutan kolom
        $longitudePos = strpos($html, "data: 'attributes.longitude'");
        $latitudePos = strpos($html, "data: 'attributes.latitude'");
        
        $this->assertNotFalse($longitudePos, 'Longitude column configuration not found');
        $this->assertNotFalse($latitudePos, 'Latitude column configuration not found');
        $this->assertLessThan($latitudePos, $longitudePos, 'Longitude should appear before latitude in column configuration');
    }

    /** @test */
    public function it_configures_api_filter_with_rtm_id_for_coordinate_data()
    {
        // Mock data untuk testing view
        $mockData = (object) [
            'rtm_id' => 'TEST_RTM_123',
            'no_kartu_rumah' => 'KRT001',
            'nama_kepala_keluarga' => 'Budi Santoso',
            'alamat' => 'Jl. Contoh No. 123',
            'jumlah_anggota' => 4,
            'jumlah_kk' => 1,
        ];

        // Render view dengan mock data
        $view = ViewFacade::make('data_pokok.data_presisi.pangan.detail', ['data' => $mockData]);
        $html = $view->render();

        // Assert bahwa filter API menggunakan rtm_id yang benar untuk mengambil data koordinat
        $this->assertStringContainsString('url.searchParams.set("filter[rtm_id]", "TEST_RTM_123")', $html);
        
        // Assert bahwa endpoint API untuk data presisi pangan dikonfigurasi dengan benar
        $this->assertStringContainsString('/api/v1/data-presisi/pangan', $html);
    }

    /** @test */
    public function it_renders_household_info_table_with_correct_data_structure()
    {
        // Mock data untuk testing view
        $mockData = (object) [
            'rtm_id' => '12345',
            'no_kartu_rumah' => 'KRT001',
            'nama_kepala_keluarga' => 'Budi Santoso',
            'alamat' => 'Jl. Contoh No. 123',
            'jumlah_anggota' => 4,
            'jumlah_kk' => 1,
        ];

        // Render view dengan mock data
        $view = ViewFacade::make('data_pokok.data_presisi.pangan.detail', ['data' => $mockData]);
        $html = $view->render();

        // Assert bahwa informasi rumah tangga ditampilkan dengan benar
        $this->assertStringContainsString('KRT001', $html);
        $this->assertStringContainsString('Budi Santoso', $html);
        $this->assertStringContainsString('Jl. Contoh No. 123', $html);
        $this->assertStringContainsString('4', $html); // jumlah anggota
        $this->assertStringContainsString('1', $html); // jumlah kk
        
        // Assert struktur tabel rincian suplemen
        $this->assertStringContainsString('tabel-rincian', $html);
        $this->assertStringContainsString('No Kartu Rumah Tangga (KRT)', $html);
    }

    /** @test */
    public function it_has_proper_datatable_initialization_for_coordinate_display()
    {
        // Mock data untuk testing view
        $mockData = (object) [
            'rtm_id' => '12345',
            'no_kartu_rumah' => 'KRT001',
            'nama_kepala_keluarga' => 'Budi Santoso',
            'alamat' => 'Jl. Contoh No. 123',
            'jumlah_anggota' => 4,
            'jumlah_kk' => 1,
        ];

        // Render view dengan mock data
        $view = ViewFacade::make('data_pokok.data_presisi.pangan.detail', ['data' => $mockData]);
        $html = $view->render();

        // Assert bahwa DataTable dikonfigurasi dengan proper settings
        $this->assertStringContainsString("$('#detail-pangan').DataTable({", $html);
        $this->assertStringContainsString('processing: true', $html);
        $this->assertStringContainsString('serverSide: true', $html);
        $this->assertStringContainsString('autoWidth: false', $html);
        
        // Assert bahwa longitude dan latitude columns ada dalam konfigurasi
        $this->assertStringContainsString("data: 'attributes.longitude'", $html);
        $this->assertStringContainsString("data: 'attributes.latitude'", $html);
    }

    /** @test */
    public function it_includes_all_expected_column_headers_in_correct_order()
    {
        // Mock data untuk testing view
        $mockData = (object) [
            'rtm_id' => '12345',
            'no_kartu_rumah' => 'KRT001',
            'nama_kepala_keluarga' => 'Budi Santoso',
            'alamat' => 'Jl. Contoh No. 123',
            'jumlah_anggota' => 4,
            'jumlah_kk' => 1,
        ];

        // Render view dengan mock data
        $view = ViewFacade::make('data_pokok.data_presisi.pangan.detail', ['data' => $mockData]);
        $html = $view->render();

        // Daftar header kolom yang diharapkan sesuai urutan
        $expectedHeaders = [
            'NO',
            'NIK', 
            'NOMOR KK',
            'NAMA',
            'JENIS LAHAN',
            'LUAS LAHAN',
            'LUAS TANAM',
            'STATUS LAHAN',
            'KOMODITI UTAMA TANAMAN PANGAN',
            'KOMODITI TANAMAN PANGAN LAINNYA',
            'JUMLAH BERDASARKAN JENIS KOMODITI',
            'USIA KOMODITI',
            'JENIS PETERNAKAN',
            'JUMLAH POPULASI',
            'JENIS PERIKANAN',
            'FREKWENSI MAKANAN PERHARI',
            'FREKWENSI KONSUMSI SAYUR PERHARI', 
            'FREKWENSI KONSUMSI BUAH PERHARI',
            'FREKWENSI KONSUMSI DAGING PERHARI',
            'LONGITUDE',
            'LATITUDE',
            'TANGGAL PENGISIAN',
            'STATUS PENGISIAN'
        ];

        // Assert bahwa semua header ada dalam HTML
        foreach ($expectedHeaders as $header) {
            $this->assertStringContainsString("<th>{$header}</th>", $html);
        }

        // Assert urutan longitude dan latitude (longitude harus sebelum latitude)
        $longitudeHeaderPos = strpos($html, '<th>LONGITUDE</th>');
        $latitudeHeaderPos = strpos($html, '<th>LATITUDE</th>');
        
        $this->assertLessThan($latitudeHeaderPos, $longitudeHeaderPos);
    }

    /** @test */
    public function it_validates_coordinate_columns_are_not_orderable()
    {
        // Mock data untuk testing view
        $mockData = (object) [
            'rtm_id' => '12345',
            'no_kartu_rumah' => 'KRT001',
            'nama_kepala_keluarga' => 'Budi Santoso',
            'alamat' => 'Jl. Contoh No. 123',
            'jumlah_anggota' => 4,
            'jumlah_kk' => 1,
        ];

        // Render view dengan mock data
        $view = ViewFacade::make('data_pokok.data_presisi.pangan.detail', ['data' => $mockData]);
        $html = $view->render();

        // Assert bahwa longitude dan latitude dikonfigurasi sebagai non-orderable
        // Ini penting karena koordinat biasanya tidak perlu diurutkan
        $this->assertStringContainsString("{ data: 'attributes.longitude', orderable: false }", $html);
        $this->assertStringContainsString("{ data: 'attributes.latitude', orderable: false }", $html);
        
        // Assert bahwa semua kolom selain nomor urut dikonfigurasi sebagai non-orderable
        $columnDefPattern = '/targets: \[0, 1, 2, 3, 4, 5\],\s*orderable: false,\s*searchable: false/';
        $this->assertMatchesRegularExpression($columnDefPattern, $html);
    }
}
