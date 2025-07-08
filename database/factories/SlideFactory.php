<?php

namespace Database\Factories;

use App\Models\CMS\Slide;
use Illuminate\Database\Eloquent\Factories\Factory;

class SlideFactory extends Factory
{
    protected $model = Slide::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'url' => $this->faker->optional()->url(),
            'thumbnail' => $this->faker->imageUrl(800, 400, 'nature', true, 'Slide'),
            'description' => $this->faker->optional()->paragraph(3),
            'state' => $this->faker->boolean(), // menghasilkan true/false (sesuai cast boolean dan digits_between:0,1)
        ];
    }
}
