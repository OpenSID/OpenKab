<?php

namespace Database\Factories;

use App\Models\Config;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppModelsConfig>
 */
class ConfigFactory extends Factory
{
    protected $model = Config::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'app_key' => 'base64:'.base64_encode(random_bytes(32)),
            'nama_desa' => fake()->city,
            'kode_desa' => fake()->randomNumber(8),
            'kode_pos' => fake()->postcode,
            'nama_kecamatan' => fake()->city,
            'kode_kecamatan' => fake()->randomNumber(6),
            'nama_kepala_camat' => fake()->name,
            'nip_kepala_camat' => fake()->numberBetween(1000000000000000, 9999999999999999),
            'nama_kabupaten' => fake()->city,
            'kode_kabupaten' => fake()->randomNumber(4),
            'nama_propinsi' => fake()->city,
            'kode_propinsi' => fake()->randomNumber(2),
        ];
    }
}
