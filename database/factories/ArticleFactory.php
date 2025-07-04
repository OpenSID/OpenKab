<?php

namespace Database\Factories;

use App\Models\CMS\Article;
use App\Models\CMS\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'category_id'  => Category::factory(), // atau sesuaikan ID yang valid
            'published_at' => $this->faker->date(),
            'slug'         => $this->faker->unique()->slug(),
            'title'        => $this->faker->sentence(6),
            'thumbnail'    => $this->faker->imageUrl(640, 480, 'nature', true, 'thumbnail'),
            'content'      => $this->faker->paragraphs(3, true),
            'state'        => $this->faker->randomElement([0, 1]), // 0: draft, 1: terbit
        ];
    }
}
