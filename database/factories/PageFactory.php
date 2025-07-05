<?php

namespace Database\Factories;

use App\Models\CMS\Page;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Enums\StatusEnum;

class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        $title = $this->faker->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'published_at' => Carbon::now()->subDays(rand(0, 30)),
            'thumbnail' => $this->faker->imageUrl(640, 480, 'business', true, 'Page'),
            'content' => $this->faker->paragraphs(3, true),
            'state' => $this->faker->randomElement([StatusEnum::aktif, StatusEnum::tidakAktif]),
        ];
    }
}
