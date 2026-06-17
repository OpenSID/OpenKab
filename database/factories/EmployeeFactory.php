<?php

namespace Database\Factories;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'identity_number' => $this->faker->unique()->numerify('##############'),
            'email' => $this->faker->unique()->safeEmail(),
            'description' => $this->faker->sentence(),
            'phone' => $this->faker->phoneNumber(),
            'foto' => null,
            'position_id' => null,
            'department_id' => null,
            'user_id' => null,
        ];
    }
}
