<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\User;

final class SessionState
{
    private const STORAGE_PATH = __DIR__ . '/.session_state.json';

    public static function saveForUser(User $user): void
    {
        $state = [
            'user_id' => $user->id,
            'email' => $user->email,
            'created_at' => now()->toIso8601String(),
        ];

        file_put_contents(self::STORAGE_PATH, json_encode($state, JSON_PRETTY_PRINT));
    }

    public static function load(): ?array
    {
        if (! file_exists(self::STORAGE_PATH)) {
            return null;
        }

        $content = file_get_contents(self::STORAGE_PATH);
        $state = json_decode($content, true);

        if (! is_array($state) || ! isset($state['user_id'])) {
            return null;
        }

        return $state;
    }

    public static function clear(): void
    {
        if (file_exists(self::STORAGE_PATH)) {
            unlink(self::STORAGE_PATH);
        }
    }

    public static function getOrCreateUser(string $emailPrefix = 'pest-session'): User
    {
        $email = $emailPrefix . '@' . config('app.url', 'localhost') . '.test';
        $existing = User::where('email', $email)->first();

        if ($existing) {
            return $existing;
        }

        return User::factory()->create([
            'email' => $email,
            'password' => 'PestTest123!',
        ]);
    }

    public static function loginAs(User $user, ?\Pest\Browser\Api\AwaitableWebpage $page = null): \Pest\Browser\Api\AwaitableWebpage
    {
        $result = visit("/_pest/login/{$user->id}");
        self::saveForUser($user);

        return $result;
    }

    public static function restoreSession(): ?\Pest\Browser\Api\AwaitableWebpage
    {
        $state = self::load();

        if ($state === null) {
            return null;
        }

        $user = User::find($state['user_id']);

        if ($user === null) {
            self::clear();

            return null;
        }

        return visit("/_pest/login/{$user->id}");
    }
}
