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

    /**
     * Assign administrator role to user.
     */
    public static function assignAdminRole(User $user): void
    {
        setPermissionsTeamId(1);
        $user->assignRole('administrator');
    }

    public static function loginAs(User $user)
    {
        $result = visit("/_pest/login/{$user->id}");
        self::saveForUser($user);

        return $result;
    }

    public static function restoreSession()
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

    /**
     * Get or create admin user, assign role, and save session.
     */
    public static function loginAdminUser(): User
    {
        $email = 'pest-smoke@opendesa.test';
        $existing = User::where('email', $email)->first();

        if ($existing) {
            // Ensure no forced password reset blocks navigation
            if ($existing->requiresPasswordReset()) {
                $existing->update([
                    'force_password_reset' => false,
                    'password_expires_at' => null,
                ]);
            }
            self::saveForUser($existing);

            return $existing;
        }

        $user = User::create([
            'email' => $email,
            'name' => 'Pest Smoke User',
            'password' => 'password',
            'username' => 'pestsmoke',
            'force_password_reset' => false,
            'password_expires_at' => null,
        ]);
        self::assignAdminRole($user);
        self::saveForUser($user);

        return $user;
    }

    /**
     * Login as user and navigate to path.
     */
    public static function loginAndNavigate(User $user, string $path, array $options = [])
    {
        $page = visit("/_pest/login/{$user->id}");
        self::saveForUser($user);

        // If login already redirected to target path, no need to navigate again
        if ($page->url() === url($path)) {
            return $page;
        }

        return $page->navigate($path, $options);
    }

    /**
     * Apply wilayah filter by kabupaten code.
     */
    public static function applyFilter($page, string $kodeKabupaten): void
    {
        $page->script('
            $("#filter_kabupaten").val("' . $kodeKabupaten . '").trigger("change");
            $("#bt_filter").click();
        ');
        $page->wait(1500);
    }

    /**
     * Clear wilayah filter.
     */
    public static function clearFilter($page): void
    {
        $page->script('
            $("#filter_kabupaten").val(null).trigger("change");
            $("#bt_filter").click();
        ');
        $page->wait(1000);
    }
}
