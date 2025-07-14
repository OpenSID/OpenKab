<?php

namespace Database\Factories;

use App\Models\CMS\Download;
use Illuminate\Database\Eloquent\Factories\Factory;

class DownloadFactory extends Factory
{
    protected $model = Download::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'url' => $this->faker->optional()->url(), // Bisa null atau url valid
            'description' => $this->faker->paragraph(2),
            'state' => $this->faker->boolean(), // Sesuai validasi: required|boolean
        ];
    }
}
