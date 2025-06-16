<?php

namespace Tests\Feature;

use Tests\BaseTestCase;

class AdminWebControllerTest extends BaseTestCase
{
    /** @test */
    public function it_can_access_kategori_index()
    {
        $response = $this->get(route('master-data-artikel.kategori', ['parrent' => 0]));
        $response->assertStatus(200);
        $response->assertViewIs('master.kategori.index');
    }

    /** @test */
    public function it_can_access_kategori_create()
    {
        $response = $this->get(route('master-data-artikel.kategori-create', ['parrent' => 1]));
        $response->assertStatus(200);
        $response->assertViewIs('master.kategori.create');
    }

    /** @test */
    public function it_can_access_kategori_edit()
    {
        $response = $this->get(route('master-data-artikel.kategori-edit', ['parrent' => 0, 'id' => 1]));
        $response->assertStatus(200);
        $response->assertViewIs('master.kategori.edit');
    }
}
