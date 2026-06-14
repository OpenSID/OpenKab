<?php

namespace Tests\Feature;

use App\Http\Controllers\DataPresisiPendidikanController;
use App\Http\Requests\DetailDataPresisiPendidikanRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use PHPUnit\Framework\Attributes\Test;
use Tests\BaseTestCase;

class DataPresisiPendidikanControllerTest extends BaseTestCase
{
    private DataPresisiPendidikanController $controller;

    public function setUp(): void
    {
        parent::setUp();
        $this->controller = new DataPresisiPendidikanController;
    }

    private function createRequest(array $data = []): DetailDataPresisiPendidikanRequest
    {
        $request = app()->make(DetailDataPresisiPendidikanRequest::class);
        $request->merge($data);
        return $request;
    }

    public function test_detail_data_without_filter_returns_correct_view(): void
    {
        $response = $this->controller->detailData($this->createRequest([
            'judul' => '',
        ]));

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('data_pokok.data_presisi.pendidikan.detail_data', $response->name());

        $data = $response->getData();
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('colomn', $data);
        $this->assertEquals('Data Presisi Pendidikan - ', $data['title']);
        $this->assertEquals('', $data['colomn']);
    }

    public function test_detail_data_with_filter_returns_correct_view(): void
    {
        $response = $this->controller->detailData($this->createRequest([
            'judul' => 'Test Judul',
            'filter' => [
                'tipe' => 'kategori',
                'nilai' => 'pendidikan',
            ],
        ]));

        $this->assertInstanceOf(View::class, $response);

        $data = $response->getData();
        $this->assertEquals('Data Presisi Pendidikan - Test Judul', $data['title']);
        $this->assertEquals('kategori:pendidikan', $data['colomn']);
    }

    public function test_detail_data_with_partial_filter_returns_empty_colomn(): void
    {
        $response = $this->controller->detailData($this->createRequest([
            'judul' => 'Test Judul',
            'filter' => [
                'tipe' => 'kategori',
            ],
        ]));

        $data = $response->getData();
        $this->assertEquals('', $data['colomn']);
    }

    public function test_detail_data_title_is_sanitized_against_xss(): void
    {
        $response = $this->controller->detailData($this->createRequest([
            'judul' => '<script>alert("xss")</script>Test',
            'filter' => [],
        ]));

        $data = $response->getData();
        $this->assertStringNotContainsString('<script>', $data['title']);
        $this->assertStringContainsString('Test', $data['title']);
    }
}
