<?php

namespace Database\Factories;

use App\Models\SuplemenTerdata;
use Illuminate\Database\Eloquent\Factories\Factory;

class SuplemenTerdataFactory extends Factory
{
    protected $model = SuplemenTerdata::class;

    public function definition()
    {
        return [
            'config_id' => \App\Models\Config::factory()->create()->id ?? 1,
            'id_suplemen' => $this->faker->randomNumber(5),
            'id_terdata' => $this->faker->randomNumber(5),
            'keluarga_id' => $this->faker->randomNumber(5),
            'penduduk_id' => $this->faker->randomNumber(5),
            'sasaran' => $this->faker->randomElement([1, 0]),
            'keterangan' => $this->faker->sentence,
        ];
    }
}
