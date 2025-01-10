<?php

namespace Tests\Feature;

use App\Models\SuplemenTerdata;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SuplemenTerdataTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test delete_multiple function with valid IDs.
     */
    public function test_delete_multiple_suplemen()
    {
        // Buat data dummy
        $suplemen1 = SuplemenTerdata::factory()->create();
        $suplemen2 = SuplemenTerdata::factory()->create();

        // Pastikan data awalnya ada di database
        $this->assertDatabaseHas(SuplemenTerdata::class, ['id' => $suplemen1->id]);
        $this->assertDatabaseHas(SuplemenTerdata::class, ['id' => $suplemen2->id]);

        // Kirim permintaan delete_multiple dengan header Authorization
        $response = $this->postJson('/api/v1/suplemen/terdata/hapus', [
            'ids' => [$suplemen1->id, $suplemen2->id],  // Gunakan ID yang dihasilkan oleh factory
        ], [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.(session('api_token') ?? ''),
        ]);

        // Pastikan responsnya berhasil
        $response->assertStatus(Response::HTTP_OK)
                 ->assertJson(['success' => true]);

        // Pastikan data terhapus dari database
        $this->assertDatabaseMissing(SuplemenTerdata::class, ['id' => $suplemen1->id]);
        $this->assertDatabaseMissing(SuplemenTerdata::class, ['id' => $suplemen2->id]);
    }

    /**
     * Test delete_multiple function with invalid IDs.
     */
    public function test_delete_multiple_with_invalid_ids()
    {
        // Kirim permintaan delete_multiple dengan ID yang tidak ada di database
        $response = $this->postJson('/api/v1/suplemen/terdata/hapus', [
            'ids' => [999, 1000], // IDs yang tidak valid
        ], [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer '.(session('api_token') ?? ''),
        ]);

        // Pastikan responsnya berhasil meskipun tidak ada data yang dihapus
        $response->assertStatus(Response::HTTP_OK)
                 ->assertJson(['success' => true]);
    }
}
