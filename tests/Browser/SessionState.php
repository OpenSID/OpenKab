<?php

declare(strict_types=1);

namespace Tests\Browser;

use App\Models\User;

final class SessionState
{
    private const STORAGE_PATH = __DIR__ . '/.session_state.json';
    private const MOCK_SERVER_PID_PATH = __DIR__ . '/.mock_server.pid';
    private const MOCK_SERVER_PORT = 8001;

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
     * Start the mock API server in the background.
     */
    public static function startMockServer(): void
    {
        if (self::isMockServerRunning()) {
            return;
        }

        $mockServerScript = __DIR__ . '/mock-server.php';
        $logPath = __DIR__ . '/.mock_server.log';

        // Use PHP built-in server with router script
        $cmd = sprintf(
            'php -S %s:%d %s > %s 2>&1 & echo $!',
            '127.0.0.1',
            self::MOCK_SERVER_PORT,
            escapeshellarg($mockServerScript),
            escapeshellarg($logPath)
        );

        $pid = trim(shell_exec($cmd));

        if ($pid && is_numeric($pid)) {
            file_put_contents(self::MOCK_SERVER_PID_PATH, $pid);
            self::waitForServer(self::MOCK_SERVER_PORT, 10);
        }
    }

    /**
     * Stop the mock API server.
     */
    public static function stopMockServer(): void
    {
        if (!file_exists(self::MOCK_SERVER_PID_PATH)) {
            return;
        }

        $pid = (int) file_get_contents(self::MOCK_SERVER_PID_PATH);

        if ($pid > 0) {
            posix_kill($pid, SIGTERM);
            usleep(200000); // Wait 200ms for graceful shutdown
        }

        @unlink(self::MOCK_SERVER_PID_PATH);
    }

    /**
     * Check if mock server is running.
     */
    public static function isMockServerRunning(): bool
    {
        if (!file_exists(self::MOCK_SERVER_PID_PATH)) {
            return false;
        }

        $pid = (int) file_get_contents(self::MOCK_SERVER_PID_PATH);

        return $pid > 0 && posix_kill($pid, 0); // Signal 0 = check existence
    }

    /**
     * Wait for a server to become available on the given port.
     */
    private static function waitForServer(int $port, int $timeoutSeconds): void
    {
        $start = time();

        while ((time() - $start) < $timeoutSeconds) {
            $fp = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
            if ($fp) {
                fclose($fp);
                return;
            }
            usleep(100000); // 100ms
        }
    }
}
