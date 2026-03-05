<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\BaseTestCase;

class BantuanControllerTest extends BaseTestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_access_bantuan_index()
    {
        $response = $this->get(route('bantuan'));

        $response->assertStatus(200);
        $response->assertViewIs('bantuan.index');
    }

    /** @test */
    public function it_can_access_bantuan_detail()
    {
        $response = $this->get(route('bantuan.detail', ['id' => 1]));

        $response->assertStatus(200);
        $response->assertViewIs('bantuan.show');
        $response->assertViewHas('id', '1');
    }

    /** @test */
    public function it_can_access_bantuan_cetak()
    {
        $response = $this->get('/bantuan/cetak');

        $response->assertStatus(200);
        $response->assertViewIs('bantuan.cetak');
        $response->assertViewHas('filter');
    }

    /** @test */
    public function it_can_access_bantuan_detail_cetak_peserta()
    {
        $response = $this->get(route('bantuan.detail.cetak', ['id' => 1]));

        $response->assertStatus(200);
        $response->assertViewIs('bantuan.cetak-peserta');
        $response->assertViewHas('id', '1');
        $response->assertViewHas('filter');
    }

    /** @test */
    public function it_passes_filter_params_to_cetak_peserta()
    {
        $response = $this->get(route('bantuan.detail.cetak', ['id' => 1]) . '?search=test');

        $response->assertStatus(200);
        $response->assertViewIs('bantuan.cetak-peserta');
        $response->assertViewHas('filter', function ($filter) {
            return isset($filter['search']) && $filter['search'] === 'test';
        });
    }
}
