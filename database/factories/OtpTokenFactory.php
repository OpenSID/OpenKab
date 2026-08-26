<?php

namespace Database\Factories;

use App\Models\OtpToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OtpToken>
 */
class OtpTokenFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = OtpToken::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $channel = $this->faker->randomElement(['email', 'telegram']);
        
        return [
            'user_id' => User::factory(),
            'token_hash' => Hash::make('123456'), // Default test OTP
            'channel' => $channel,
            'identifier' => $channel === 'email' 
                ? $this->faker->email 
                : $this->faker->numerify('#########'),
            'expires_at' => now()->addMinutes(5),
            'attempts' => 0,
        ];
    }

    /**
     * Indicate that the token has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subMinutes(10),
        ]);
    }

    /**
     * Indicate that the token has reached max attempts.
     */
    public function maxAttempts(): static
    {
        return $this->state(fn (array $attributes) => [
            'attempts' => 3,
        ]);
    }

    /**
     * Set specific OTP code for testing.
     */
    public function withOtp(string $otp): static
    {
        return $this->state(fn (array $attributes) => [
            'token_hash' => Hash::make($otp),
        ]);
    }

    /**
     * Set email channel.
     */
    public function email(?string $email = null): static
    {
        return $this->state(fn (array $attributes) => [
            'channel' => 'email',
            'identifier' => $email ?? $this->faker->email,
        ]);
    }

    /**
     * Set telegram channel.
     */
    public function telegram(?string $chatId = null): static
    {
        return $this->state(fn (array $attributes) => [
            'channel' => 'telegram',
            'identifier' => $chatId ?? $this->faker->numerify('#########'),
        ]);
    }
}