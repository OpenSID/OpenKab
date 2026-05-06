<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class DataPresisiJaminanSosialControllerTest extends TestCase
{
    use WithoutMiddleware;

    public function testDetailDataWithoutParameters()
    {
        $response = $this->get('/data-presisi/jaminan-sosial/detail_data');

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.data_presisi.jaminan_sosial.detail_data');
        $response->assertViewHas('title', '');
        $response->assertViewHas('colomn', '');
    }

    public function testDetailDataWithJudul()
    {
        $judul = '<script>Test Title</script>';
        $response = $this->get('/data-presisi/jaminan-sosial/detail_data?judul=' . urlencode($judul));

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.data_presisi.jaminan_sosial.detail_data');
        $response->assertViewHas('title', htmlspecialchars(strip_tags($judul)));
        $response->assertViewHas('colomn', '');
    }

    public function testDetailDataWithFilter()
    {
        $filter = ['tipe' => 'status', 'nilai' => 'aktif'];
        $response = $this->get('/data-presisi/jaminan-sosial/detail_data?filter[tipe]=' . $filter['tipe'] . '&filter[nilai]=' . $filter['nilai']);

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.data_presisi.jaminan_sosial.detail_data');
        $response->assertViewHas('title', '');
        $response->assertViewHas('colomn', $filter['tipe'] . ':' . $filter['nilai']);
    }

    public function testDetailDataWithBothParameters()
    {
        $judul = 'Test Title & More';
        $filter = ['tipe' => 'jenis', 'nilai' => 'BPJS'];
        $response = $this->get('/data-presisi/jaminan-sosial/detail_data?judul=' . urlencode($judul) . '&filter[tipe]=' . $filter['tipe'] . '&filter[nilai]=' . $filter['nilai']);

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.data_presisi.jaminan_sosial.detail_data');
        $response->assertViewHas('title', htmlspecialchars(strip_tags($judul)));
        $response->assertViewHas('colomn', $filter['tipe'] . ':' . $filter['nilai']);
    }

    public function testDetailDataWithEmptyFilter()
    {
        $response = $this->get('/data-presisi/jaminan-sosial/detail_data?filter[tipe]=&filter[nilai]=');

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.data_presisi.jaminan_sosial.detail_data');
        $response->assertViewHas('title', '');
        $response->assertViewHas('colomn', '');
    }
}