<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\BaseTestCase;

class WilayahFilterParameterTest extends BaseTestCase
{
    #[Test]
    public function test_meta_tag_identitas_openkab_exists()
    {
        $response = $this->get(route('dasbor'));

        $response->assertStatus(200);
        $response->assertSee('name="identitas-openkab"', false);
    }

    #[Test]
    public function test_wilayah_filter_js_contains_kode_kabupaten_param()
    {
        $response = $this->get(route('dasbor'));

        $response->assertStatus(200);
        $response->assertSee('kode_kabupaten', false);
    }

    #[Test]
    public function test_wilayah_filter_js_contains_filter_kode_kabupaten_param()
    {
        $response = $this->get(route('dasbor'));

        $response->assertStatus(200);
        $response->assertSee('filter[kode_kabupaten]', false);
    }

    #[Test]
    public function test_summary_api_url_contains_kode_kabupaten_param()
    {
        $response = $this->get(route('dasbor') . '?page=summary');

        $response->assertStatus(200);
    }

    #[Test]
    public function test_peta_api_url_contains_kode_kabupaten_param()
    {
        $response = $this->get(route('dasbor') . '?page=peta');

        $response->assertStatus(200);
    }
}