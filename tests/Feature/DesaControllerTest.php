<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\BaseTestCase;

class DesaControllerTest extends BaseTestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_returns_200_on_desa_index()
    {
        $response = $this->get(route('desa.index'));

        $response->assertStatus(200);
        $response->assertViewIs('desa.index');
    }

    #[Test]
    public function it_normalizes_null_string_filter_values()
    {
        $response = $this->get(route('desa.index') . '?filter[kode_kabupaten]=5102&filter[kode_kecamatan]=&filter[kode_desa]=null&filter[nama_kabupaten]=Tabanan&filter[nama_kecamatan]=&filter[nama_desa]=');

        $response->assertStatus(200);
        $response->assertViewHas('filters', function ($filters) {
            return $filters['kode_kabupaten'] === '5102'
                && empty($filters['kode_kecamatan'])
                && $filters['kode_desa'] === null;
        });
    }

    #[Test]
    public function it_does_not_render_option_with_null_string_value()
    {
        $response = $this->get(route('desa.index') . '?filter[kode_kabupaten]=5102&filter[kode_kecamatan]=&filter[kode_desa]=null&filter[nama_kabupaten]=Tabanan&filter[nama_kecamatan]=&filter[nama_desa]=');

        $response->assertStatus(200);
        $response->assertDontSee('value="null"');
        $response->assertSee('Tabanan');
    }
}