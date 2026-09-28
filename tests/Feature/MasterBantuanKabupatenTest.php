<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\BaseTestCase;

class MasterBantuanKabupatenTest extends BaseTestCase
{
    #[Test]
    public function it_can_access_bantuan_create_and_contains_show_error_alert()
    {
        $response = $this->get(route('bantuan.create'));
        $response->assertStatus(200);
        $response->assertViewIs('master.bantuan.create');
        $response->assertSee('showErrorAlert', false);
        $response->assertSee('data.errors', false);
    }

    #[Test]
    public function it_can_access_bantuan_edit_and_contains_show_error_alert()
    {
        $bantuanId = 1;
        $response = $this->get(route('bantuan.edit', ['bantuan' => $bantuanId]));
        $response->assertStatus(200);
        $response->assertViewIs('master.bantuan.edit');
        $response->assertViewHas('id', $bantuanId);
        $response->assertSee('showErrorAlert', false);
        $response->assertSee('data.errors', false);
    }
}
