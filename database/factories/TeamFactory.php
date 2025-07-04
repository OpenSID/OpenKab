<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class TeamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'administrator',
                'synchronize',
                'superadmin_daerah',
                'kabupaten',
            ]),
            'menu' => json_encode([
                [
                    'icon' => 'fa fa-users',
                    'text' => 'Profile',
                    'url' => '/profile',
                ],
            ]),
            'menu_order' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
