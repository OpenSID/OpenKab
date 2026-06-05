<?php

namespace Tests\Feature;

use Tests\BaseTestCase;

class DetailDataPresisiAktivitasKeagamaanRequestTest extends BaseTestCase
{
    #[Test]
    public function it_validates_judul_as_nullable_string_max_255()
    {
        // Valid judul
        $response = $this->get(route('data-pokok.data-presisi-aktivitas-keagamaan.detail_data', ['judul' => 'Valid title']));
        $response->assertStatus(200);

        // Judul too long
        $longJudul = str_repeat('a', 256);
        $response = $this->get(route('data-pokok.data-presisi-aktivitas-keagamaan.detail_data', ['judul' => $longJudul]));
        $response->assertRedirect();
        $response->assertSessionHasErrors('judul');
    }

    #[Test]
    public function it_validates_filter_as_nullable_array()
    {
        // Valid filter
        $response = $this->get(route('data-pokok.data-presisi-aktivitas-keagamaan.detail_data', ['filter' => ['tipe' => 'agama_id', 'nilai' => '1']]));
        $response->assertStatus(200);

        // Invalid filter - not array
        $response = $this->get(route('data-pokok.data-presisi-aktivitas-keagamaan.detail_data', ['filter' => 'not_array']));
        $response->assertRedirect();
        $response->assertSessionHasErrors('filter');
    }

    #[Test]
    public function it_validates_filter_tipe_required_with_filter_and_in_allowed_values()
    {
        // Valid tipe
        $response = $this->get(route('data-pokok.data-presisi-aktivitas-keagamaan.detail_data', ['filter' => ['tipe' => 'agama_id', 'nilai' => '1']]));
        $response->assertStatus(200);

        $response = $this->get(route('data-pokok.data-presisi-aktivitas-keagamaan.detail_data', ['filter' => ['tipe' => 'frekwensi_mengikuti_kegiatan_setahun', 'nilai' => '1']]));
        $response->assertStatus(200);

        // Invalid tipe
        $response = $this->get(route('data-pokok.data-presisi-aktivitas-keagamaan.detail_data', ['filter' => ['tipe' => 'invalid_tipe', 'nilai' => '1']]));
        $response->assertRedirect();
        $response->assertSessionHasErrors('filter.tipe');

        // Missing tipe when filter present
        $response = $this->get(route('data-pokok.data-presisi-aktivitas-keagamaan.detail_data', ['filter' => ['nilai' => '1']]));
        $response->assertRedirect();
        $response->assertSessionHasErrors('filter.tipe');
    }

    #[Test]
    public function it_validates_filter_nilai_required_with_filter_as_string()
    {
        // Valid nilai
        $response = $this->get(route('data-pokok.data-presisi-aktivitas-keagamaan.detail_data', ['filter' => ['tipe' => 'agama_id', 'nilai' => '1']]));
        $response->assertStatus(200);

        // Missing nilai when filter present
        $response = $this->get(route('data-pokok.data-presisi-aktivitas-keagamaan.detail_data', ['filter' => ['tipe' => 'agama_id']]));
        $response->assertRedirect();
        $response->assertSessionHasErrors('filter.nilai');
    }
}