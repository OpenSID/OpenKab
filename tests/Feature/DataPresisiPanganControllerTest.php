<?php

namespace Tests\Feature;

use Tests\BaseTestCase;

class DataPresisiPanganControllerTest extends BaseTestCase
{
    #[Test]
    public function test_can_access_pangan_index_page()
    {
        $response = $this->get(route('data-pokok.data-presisi-pangan.index'));

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.data_presisi.pangan.index');
        $response->assertViewHas('title', 'Data Presisi Pangan');
    }

    #[Test]
    public function test_detail_data_with_valid_filter_parameters()
    {
        $response = $this->get(route('data-pokok.data-presisi-pangan.detail_data') . '?judul=Test%20Judul&filter[tipe]=kategori_pangan&filter[nilai]=beras');

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.data_presisi.pangan.detail_data');
        $response->assertViewHas('title', 'Data Presisi Pangan - Test Judul');
        $response->assertViewHas('colomn', 'kategori_pangan:beras');
    }

    #[Test]
    public function test_detail_data_with_missing_filter_tipe()
    {
        $response = $this->get(route('data-pokok.data-presisi-pangan.detail_data') . '?judul=Test%20Judul&filter[nilai]=beras');

        $response->assertStatus(200);
        $response->assertViewHas('colomn', '');
    }

    #[Test]
    public function test_detail_data_with_missing_filter_nilai()
    {
        $response = $this->get(route('data-pokok.data-presisi-pangan.detail_data') . '?judul=Test%20Judul&filter[tipe]=kategori_pangan');

        $response->assertStatus(200);
        $response->assertViewHas('colomn', '');
    }

    #[Test]
    public function test_detail_data_with_empty_filter_tipe()
    {
        $response = $this->get(route('data-pokok.data-presisi-pangan.detail_data') . '?judul=Test%20Judul&filter[tipe]=&filter[nilai]=beras');

        $response->assertStatus(200);
        $response->assertViewHas('colomn', '');
    }

    #[Test]
    public function test_detail_data_with_empty_filter_nilai()
    {
        $response = $this->get(route('data-pokok.data-presisi-pangan.detail_data') . '?judul=Test%20Judul&filter[tipe]=kategori_pangan&filter[nilai]=');

        $response->assertStatus(200);
        $response->assertViewHas('colomn', '');
    }

    #[Test]
    public function test_detail_data_without_filter_parameter()
    {
        $response = $this->get(route('data-pokok.data-presisi-pangan.detail_data') . '?judul=Test%20Judul');

        $response->assertStatus(200);
        $response->assertViewHas('colomn', '');
    }

    #[Test]
    public function test_detail_data_without_judul_parameter()
    {
        $response = $this->get(route('data-pokok.data-presisi-pangan.detail_data') . '?filter[tipe]=kategori_pangan&filter[nilai]=beras');

        $response->assertStatus(200);
        $response->assertViewHas('title', 'Data Presisi Pangan - ');
        $response->assertViewHas('colomn', 'kategori_pangan:beras');
    }

    #[Test]
    public function test_cetak_with_filter_parameters()
    {
        $response = $this->get(route('data-pokok.data-presisi-pangan.cetak') . '?filter[tipe]=kategori_pangan&filter[nilai]=beras&tahun=2024');

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.data_presisi.pangan.cetak');
        $response->assertViewHas('filter');
    }

    #[Test]
    public function test_cetak_without_parameters()
    {
        $response = $this->get(route('data-pokok.data-presisi-pangan.cetak'));

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.data_presisi.pangan.cetak');
    }

    #[Test]
    public function test_detail_data_column_format_with_special_characters()
    {
        $response = $this->get(route('data-pokok.data-presisi-pangan.detail_data') . '?judul=Test%20Judul&filter[tipe]=kategori_pangan&filter[nilai]=beras-organik');

        $response->assertStatus(200);
        $response->assertViewHas('colomn', 'kategori_pangan:beras-organik');
    }

    #[Test]
    public function test_detail_data_column_format_with_spaces()
    {
        $response = $this->get(route('data-pokok.data-presisi-pangan.detail_data') . '?judul=Test%20Judul&filter[tipe]=kategori%20pangan&filter[nilai]=beras%20merah');

        $response->assertStatus(200);
        $response->assertViewHas('colomn', 'kategori pangan:beras merah');
    }
}
