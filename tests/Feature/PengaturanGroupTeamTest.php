<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Http\Response;
use Tests\TestCase;

class PengaturanGroupTeamTest extends TestCase
{
    protected $team;

    public function setUp(): void
    {
        parent::setUp();

        $user = User::inRandomOrder()->first();

        Sanctum::actingAs($user);

        $this->team = Team::inRandomOrder()->first();

    }

    /**
     * A basic feature test example.
     */
    public function test_get_data_group(): void
    {
        $url = '/api/v1/pengaturan/group';

        $response = $this->getJson($url);

        // Pastikan responsnya berhasil
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'attributes' => [                        
                        'name',
                        'menu' => [],                    
                    ]
                ],
            ],
        ]);
    }

    public function test_get_data_group_by_id(): void
    {
        $url = '/api/v1/pengaturan/group/show/'.$this->team->id;

        $response = $this->getJson($url);

        // Pastikan responsnya berhasil
        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonStructure([
            'data' => [
                'type',
                'id',
                'attributes' => [
                    'name',
                    'menu' => [],    
                    'menu_order',
                    'created_at',
                    'updated_at',
                    'role' => [
                        '*' => [
                            'id',
                            'team_id',
                            'name',
                            'guard_name',
                            'created_at',
                            'updated_at',
                            'permissions' => []
                        ]
                    ]
                ]
            ]
        ]);

    }

    public function test_get_data_group_menu(): void
    {
        $url = '/api/v1/pengaturan/group/menu';

        $response = $this->getJson($url);

        // Pastikan responsnya berhasil
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => [
                    'text',
                    'icon',
                    'permission',
                ]
            ]
        ]);
    }

    public function test_insert_and_delete_team_success()
    {
        // 1. Kirim request POST ke API untuk menambahkan data
        $response = $this->postJson('/api/v1/pengaturan/group', [
            'name' => 'Test Menu',
            'menu' => [
                [
                    "icon" => "fa fa-users",
                    "text" => "test Menu"
                ]
            ],
            'menu_order' => null,
        ]);

        // 2. Ambil ID dari response API
        $team_id = Team::max('id');

        // 3. Pastikan data berhasil masuk ke database
        $this->assertDatabaseHas('team', ['id' => $team_id]);

        // 4. Hapus data yang baru dibuat melalui API
        $deleteResponse = $this->postJson('/api/v1/pengaturan/group/delete', [
            'id' => $team_id
        ]);

        // 5. Pastikan response sukses (200 OK)
        $deleteResponse->assertStatus(Response::HTTP_OK);

        // 6. Pastikan data benar-benar sudah terhapus
        $this->assertDatabaseMissing('team', ['id' => $team_id]);
    }


}