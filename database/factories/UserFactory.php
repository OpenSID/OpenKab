<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // return [
        //     'name' => fake()->name(),
        //     'email' => fake()->unique()->safeEmail(),
        //     'email_verified_at' => now(),
        //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        //     'remember_token' => Str::random(10),
        // ];

        return [
            // ⚠️ WAJIB `unique()` + sufiks acak. Kolom `username` punya batasan unik
            // di DB dan basis data pengembangan sudah berisi ratusan baris,
            // sehingga `unique()` saja (yang hanya men unik dalam satu proses)
            // TIDAK cukup — nama dari kumpulan faker bisa saja sudah ada.
            // Sufiks 6 karakter acak membuat bentrok praktis mustahil tanpa
            // mengubah bentuk nama yang dipakai test lain.
            'username' => $this->faker->unique()->userName.'.'.Str::lower(Str::random(6)),
            'password' => Hash::make('password'), // default password
            'email' => $this->faker->unique()->safeEmail.'+'.Str::lower(Str::random(6)).'@example.test',
            'last_login' => now(),
            'email_verified_at' => now(),
            'active' => 1,
            'name' => $this->faker->name,
            'company' => $this->faker->company,
            'phone' => $this->faker->phoneNumber,
            'foto' => 'default.png',
            'kode_kabupaten' => $this->faker->postcode,
            'remember_token' => Str::random(10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     *
     * @return static
     */
    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
