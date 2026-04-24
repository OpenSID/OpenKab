<?php

namespace Tests\Feature;

use App\Http\Controllers\DataPresisiSandangController;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Tests\BaseTestCase;

class DataPresisiSandangControllerTest extends BaseTestCase
{
    private DataPresisiSandangController $controller;

    public function setUp(): void
    {
        parent::setUp();
        $this->controller = new DataPresisiSandangController;        
    }   

    public function test_detail_data_without_filter_returns_correct_view(): void
    {
        $response = $this->controller->detailData(new Request());

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('data_pokok.data_presisi.sandang.detail_data', $response->name());

        $data = $response->getData();
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('colomn', $data);
        $this->assertEquals('Data Presisi Sandang ', $data['title']);
        $this->assertEquals('', $data['colomn']);
    }

    public function test_detail_data_with_filter_returns_correct_view(): void
    {        
        $response = $this->controller->detailData(new Request([
            'judul' => 'Test Judul',
            'filter' => [
                'tipe' => 'kategori',
                'nilai' => 'pakaian',
            ],
        ]));

        $this->assertInstanceOf(View::class, $response);

        $data = $response->getData();
        $this->assertEquals('Data Presisi Sandang Test Judul', $data['title']);
        $this->assertEquals('kategori:pakaian', $data['colomn']);
    }

    public function test_detail_data_with_partial_filter_returns_empty_colomn(): void
    {
        request()->merge([
            'judul' => 'Test Judul',
            'filter' => [
                'tipe' => 'kategori',
            ],
        ]);

        $response = $this->controller->detailData(new Request());

        $data = $response->getData();
        $this->assertEquals('', $data['colomn']);
    }    
}
