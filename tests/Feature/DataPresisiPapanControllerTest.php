<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\BaseTestCase;

class DataPresisiPapanControllerTest extends BaseTestCase
{
    use DatabaseTransactions;

    public function test_detail_data_returns_success()
    {
        $user = User::first();
        $response = $this->actingAsAdmin($user)
            ->get(route('data-pokok.data-presisi-papan.detail_data', [
                'judul' => 'Test Judul',
                'filter' => [
                    'tipe' => 'test_tipe',
                    'nilai' => 'test_nilai'
                ]
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.data_presisi.papan.detail_data');
    }

    public function test_detail_data_returns_success_without_filter()
    {
        $user = User::first();
        $response = $this->actingAsAdmin($user)
            ->get(route('data-pokok.data-presisi-papan.detail_data', [
                'judul' => 'Test Judul Tanpa Filter'
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('data_pokok.data_presisi.papan.detail_data');
    }
}