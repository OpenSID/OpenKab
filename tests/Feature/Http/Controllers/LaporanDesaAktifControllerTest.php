<?php

namespace Tests\Feature\Http\Controllers;

use Tests\BaseTestCase;

class LaporanDesaAktifControllerTest extends BaseTestCase
{    

    /**
     * Test index method returns correct view and data.
     */
    public function test_index_returns_correct_view_with_title()
    {
        $response = $this->get(route('laporan.desa-aktif.index'));

        $response->assertStatus(200)
            ->assertViewIs('laporan.desa_aktif.index')
            ->assertViewHas('title', 'Laporan Desa Aktif');
    }    
}
