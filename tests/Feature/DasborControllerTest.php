<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Mockery;
use Tests\BaseTestCase;

class DasborControllerTest extends BaseTestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_can_access_dasbor_index()
    {
        $response = $this->get(route('dasbor'));

        $response->assertStatus(200);
        $response->assertViewIs('dasbor.index');
        $response->assertViewHas('data');
        $response->assertViewHas('categoriesItems');
    }

    /** @test */
    public function it_returns_correct_categories_items_structure()
    {
        $response = $this->get(route('dasbor'));

        $response->assertViewHas('categoriesItems', function ($categoriesItems) {
            return is_array($categoriesItems) &&
                count($categoriesItems) === 4 &&
                collect($categoriesItems)->every(function ($item) {
                    return isset($item['key'], $item['url'], $item['text'], $item['value'], $item['icon']);
                });
        });
    }

    /** @test */
    public function it_has_correct_category_keys()
    {
        $response = $this->get(route('dasbor'));

        $response->assertViewHas('categoriesItems', function ($categoriesItems) {
            $keys = collect($categoriesItems)->pluck('key')->toArray();

            return $keys === ['kecamatan', 'desa', 'penduduk', 'keluarga'];
        });
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
