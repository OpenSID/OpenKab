<?php

namespace Database\Factories;

use App\Models\CMS\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

class VisitFactory extends Factory
{
    protected $model = Visit::class;

    public function definition(): array
    {
        return [
            'method' => $this->faker->randomElement(['GET', 'POST']),
            'request' => [
                'query' => [],
                'body' => [],
            ],
            'url' => $this->faker->url(),
            'referer' => $this->faker->url(),
            'languages' => ['en-US', 'id-ID'],
            'useragent' => $this->faker->userAgent(),
            'headers' => [
                'accept' => 'application/json',
                'host' => 'localhost',
            ],
            'device' => $this->faker->randomElement(['Desktop', 'Mobile', 'Tablet']),
            'platform' => $this->faker->randomElement(['Windows', 'Linux', 'macOS']),
            'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari']),
            'ip' => $this->faker->ipv4(),
            'visitor_id' => null,
            'visitor_type' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
