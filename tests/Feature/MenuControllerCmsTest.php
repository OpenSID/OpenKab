<?php

namespace Tests\Feature;

use App\Models\CMS\Category;
use App\Models\CMS\Page;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\BaseTestCase;

class MenuControllerCmsTest extends BaseTestCase
{
    use DatabaseTransactions;

    /** @test
     * Menguji halaman index menu dapat ditampilkan tanpa error
     */
    public function test_index_menampilkan_halaman_daftar_menu()
    {
        // Arrange: siapkan page dan category
        Page::factory()->count(2)->create();
        Category::factory()->count(2)->create();

        // Act: akses halaman index
        $response = $this->get(route('menus.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewIs('menus.index');
        $response->assertViewHas('menus');
        $response->assertViewHas('sourceItem');
    }

    /** @test
     * Menguji penyimpanan data menu baru berhasil dan redirect sesuai jenis menu
     */
    public function test_store_menyimpan_menu_baru_dan_redirect_sesuai_jenis()
    {
        // Arrange
        $payload = [
            'menu_type' => 1,
            'position' => 1,
            'json_menu' => json_encode([
                [
                    'text' => 'Menu Baru',
                    'href' => '/menu-baru',
                    'icon' => 'fas fa-star',
                ],
            ]),
        ];

        // Act
        $response = $this->post(route('menus.store'), $payload);

        // Assert
        $response->assertRedirect(route('menus.index'));
        $this->assertDatabaseHas('menus', ['name' => 'Menu Baru']);
        $this->assertEquals('Menu berhasil disimpan.', session('success'));
    }

    /** @test
     * Menguji redirect store jika tipe menu 2
     */
    public function test_store_redirect_ke_index_dengan_type_dua()
    {
        $payload = [
            'menu_type' => 2,
            'position' => 1,
            'json_menu' => json_encode([
                [
                    'text' => 'Menu Statistik',
                    'href' => '/menu-statistik',
                    'icon' => 'fas fa-star',
                ],
            ]),
        ];

        $response = $this->post(route('menus.store'), $payload);

        $response->assertRedirect(route('menus.index').'?type=2');
        $this->assertDatabaseHas('menus', ['name' => 'Menu Statistik']);
    }

    /** @test
     * Menguji jika parameter menu_type tidak dikirim maka default ke 1
     */
    public function test_store_menu_type_default_ke_satu_jika_null()
    {
        $payload = [
            'menu_type' => 1,
            'position' => 1,
            'json_menu' => json_encode([
                [
                    'text' => 'Menu Default',
                    'href' => '/menu-default',
                    'icon' => 'fas fa-star',
                ],
            ]),
        ];

        $response = $this->post(route('menus.store'), $payload);

        $response->assertRedirect(route('menus.index'));
        $this->assertDatabaseHas('menus', ['name' => 'Menu Default']);
    }
}
