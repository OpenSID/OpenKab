<?php

namespace Tests\Feature;

use App\Http\Controllers\DataPresisiSeniBudayaController;
use App\Http\Requests\DetailDataPresisiSeniBudayaRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Tests\BaseTestCase;

/**
 * Test untuk DataPresisiSeniBudayaController
 */
class DataPresisiSeniBudayaControllerTest extends BaseTestCase
{
    private DataPresisiSeniBudayaController $controller;

    public function setUp(): void
    {
        parent::setUp();
        $this->controller = new DataPresisiSeniBudayaController;
    }

    /**
     * Helper untuk membuat request dengan data yang diberikan
     */
    private function createRequest(array $data = []): DetailDataPresisiSeniBudayaRequest
    {
        $request = app()->make(DetailDataPresisiSeniBudayaRequest::class);
        $request->merge($data);

        return $request;
    }

    /**
     * Test halaman utama data seni budaya
     */
    public function test_index_returns_correct_view(): void
    {
        $response = $this->controller->index();

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('data_pokok.data_presisi.seni_budaya.index', $response->name());

        $data = $response->getData();
        $this->assertArrayHasKey('title', $data);
        $this->assertEquals('Data Presisi Seni Budaya', $data['title']);
    }

    /**
     * Test detail data tanpa filter mengembalikan view yang benar
     */
    public function test_detail_data_without_filter_returns_correct_view(): void
    {
        $response = $this->controller->detailData($this->createRequest([
            'judul' => '',
        ]));

        $this->assertInstanceOf(View::class, $response);
        $this->assertEquals('data_pokok.data_presisi.seni_budaya.detail_data', $response->name());

        $data = $response->getData();
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('colomn', $data);
        $this->assertEquals('Seni Budaya - ', $data['title']);
        $this->assertEquals('', $data['colomn']);
    }

    /**
     * Test detail data dengan filter mengembalikan view yang benar
     */
    public function test_detail_data_with_filter_returns_correct_view(): void
    {
        $response = $this->controller->detailData($this->createRequest([
            'judul' => 'Test Judul',
            'filter' => [
                'tipe' => 'kategori',
                'nilai' => 'seni',
            ],
        ]));

        $this->assertInstanceOf(View::class, $response);

        $data = $response->getData();
        $this->assertEquals('Seni Budaya - Test Judul', $data['title']);
        $this->assertEquals('kategori:seni', $data['colomn']);
    }

    /**
     * Test detail data dengan filter khusus untuk jenis seni yang dikuasai
     * harus menambahkan suffix .sub_jenis_seni
     */
    public function test_detail_data_with_jenis_seni_filter_adds_sub_jenis_seni(): void
    {
        $response = $this->controller->detailData($this->createRequest([
            'judul' => 'Test Judul',
            'filter' => [
                'tipe' => 'jenis_seni_yang_dikuasai',
                'nilai' => 'batik',
            ],
        ]));

        $data = $response->getData();
        $this->assertEquals('jenis_seni_yang_dikuasai.sub_jenis_seni:batik', $data['colomn']);
    }

    /**
     * Test detail data dengan filter parsial mengembalikan colomn kosong
     */
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

    /**
     * Test detail data dengan filter nilai kosong mengembalikan colomn kosong
     */
    public function test_detail_data_with_empty_nilai_filter_returns_empty_colomn(): void
    {
        $response = $this->controller->detailData($this->createRequest([
            'judul' => 'Test Judul',
            'filter' => [
                'tipe' => 'kategori',
                'nilai' => '',
            ],
        ]));

        $data = $response->getData();
        $this->assertEquals('', $data['colomn']);
    }

    /**
     * Test judul disanitasi untuk mencegah XSS
     */
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

    /**
     * Test detail data tanpa parameter judul
     */
    public function test_detail_data_without_judul_returns_empty_title(): void
    {
        $response = $this->controller->detailData($this->createRequest([]));

        $data = $response->getData();
        $this->assertEquals('Seni Budaya - ', $data['title']);
    }
}
