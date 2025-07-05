<?php

namespace Database\Factories;

use App\Models\CMS\Page;
use App\Models\Enums\StatusEnum;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(4);
        $slug = Str::slug($title);

        return [
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'slug' => $slug,
            'title' => $title,
            'thumbnail' => $this->faker->imageUrl(640, 480, 'business', true, 'thumbnail'),
            'content' => $this->faker->paragraphs(3, true),
            'state' => $this->faker->randomElement([StatusEnum::aktif, StatusEnum::tidakAktif]),
        ];
    }
}
