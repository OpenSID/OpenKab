<?php

namespace Tests\Feature\Http\Controllers;

use Tests\BaseTestCase;

class DataPresisiAktivitasKeagamaanControllerTest extends BaseTestCase
{
    #[Test]
    public function it_can_access_detail_data_page()
    {
        $response = $this->get(route('data-pokok.data-presisi-aktivitas-keagamaan.detail_data'));

        $response->assertStatus(200)
            ->assertViewIs('data_pokok.data_presisi.aktivitas_keagamaan.detail_data')
            ->assertViewHas('title')
            ->assertViewHas('colomn');
    }

    #[Test]
    public function it_handles_judul_parameter()
    {
        $judul = 'Test Title <script>alert("xss")</script>';
        $response = $this->get(route('data-pokok.data-presisi-aktivitas-keagamaan.detail_data', ['judul' => $judul]));

        $response->assertStatus(200)
            ->assertViewHas('title', htmlspecialchars(strip_tags($judul)))
            ->assertViewHas('colomn', '');
    }

    #[Test]
    public function it_handles_filter_parameter()
    {
        $filter = [
            'tipe' => 'agama_id',
            'nilai' => '1'
        ];
        $response = $this->get(route('data-pokok.data-presisi-aktivitas-keagamaan.detail_data', ['filter' => $filter]));

        $response->assertStatus(200)
            ->assertViewHas('colomn', 'agama_id:1');
    }

    #[Test]
    public function it_handles_filter_with_different_tipe()
    {
        $filter = [
            'tipe' => 'frekwensi_mengikuti_kegiatan_setahun',
            'nilai' => '2'
        ];
        $response = $this->get(route('data-pokok.data-presisi-aktivitas-keagamaan.detail_data', ['filter' => $filter]));

        $response->assertStatus(200)
            ->assertViewHas('colomn', 'frekwensi_mengikuti_kegiatan_setahun:2');
    }

    #[Test]
    public function it_handles_empty_filter()
    {
        $response = $this->get(route('data-pokok.data-presisi-aktivitas-keagamaan.detail_data', ['filter' => []]));

        $response->assertStatus(200)
            ->assertViewHas('colomn', '');
    }
}