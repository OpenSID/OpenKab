<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\BaseTestCase;

class AdminWebControllerTest extends BaseTestCase
{
    #[Test]
    public function it_can_access_kategori_index()
    {
        $response = $this->get(route('master-data-artikel.kategori', ['parrent' => 0]));
        $response->assertStatus(200);
        $response->assertViewIs('master.kategori.index');
    }

    #[Test]
    public function it_can_access_kategori_create()
    {
        $response = $this->get(route('master-data-artikel.kategori-create', ['parrent' => 1]));
        $response->assertStatus(200);
        $response->assertViewIs('master.kategori.create');
    }

    #[Test]
    public function it_can_access_kategori_edit()
    {
        $response = $this->get(route('master-data-artikel.kategori-edit', ['parrent' => 0, 'id' => 1]));
        $response->assertStatus(200);
        $response->assertViewIs('master.kategori.edit');
    }
}
