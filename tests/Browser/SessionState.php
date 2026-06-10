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
     * Get or create admin user, assign role, and save session.
     */
    public static function loginAdminUser(): User
    {
        $email = 'pest-smoke@opendesa.test';
        $existing = User::where('email', $email)->first();

        if ($existing) {
            self::saveForUser($existing);

            return $existing;
        }

        $user = User::create([
            'email' => $email,
            'name' => 'Pest Smoke User',
            'password' => 'password',
            'username' => 'pestsmoke',
        ]);
        self::assignAdminRole($user);
        self::saveForUser($user);

        return $user;
    }

    /**
     * Login as user and navigate to path.
     */
    public static function loginAndNavigate(User $user, string $path)
    {
        $page = visit("/_pest/login/{$user->id}");
        self::saveForUser($user);

        return $page->navigate($path);
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

    /**
     * Start the mock API server in the background.
     *
     * @throws \RuntimeException if the server fails to start
     */
    public static function startMockServer(): void
    {
        if (self::isMockServerRunning()) {
            return;
        }

        self::killStaleProcess();

        $mockServerScript = __DIR__ . '/mock-server.php';
        $logPath = __DIR__ . '/.mock_server.log';

        $cmd = sprintf(
            'php -S %s:%d %s > %s 2>&1 & echo $!',
            '127.0.0.1',
            self::MOCK_SERVER_PORT,
            escapeshellarg($mockServerScript),
            escapeshellarg($logPath)
        );

        $pid = trim(shell_exec($cmd));

        if (! $pid || ! is_numeric($pid)) {
            throw new \RuntimeException(
                "Failed to start mock server. Command: {$cmd}"
            );
        }

        file_put_contents(self::MOCK_SERVER_PID_PATH, $pid);

        if (! self::waitForServer(self::MOCK_SERVER_PORT, 15)) {
            $logContent = file_exists($logPath) ? file_get_contents($logPath) : '(no log)';
            throw new \RuntimeException(
                "Mock server started (PID: {$pid}) but not responding on port "
                . self::MOCK_SERVER_PORT . ". Log: {$logContent}"
            );
        }
    }

    /**
     * Stop the mock API server.
     */
    public static function stopMockServer(): void
    {
        if (file_exists(self::MOCK_SERVER_PID_PATH)) {
            $pid = (int) file_get_contents(self::MOCK_SERVER_PID_PATH);

            if ($pid > 0 && posix_kill($pid, 0)) {
                posix_kill($pid, SIGTERM);
                usleep(200000);
            }

            @unlink(self::MOCK_SERVER_PID_PATH);
        }

        self::killStaleProcess();
    }

    /**
     * Check if mock server is running and responsive.
     */
    public static function isMockServerRunning(): bool
    {
        if (! file_exists(self::MOCK_SERVER_PID_PATH)) {
            return false;
        }

        $pid = (int) file_get_contents(self::MOCK_SERVER_PID_PATH);

        if ($pid <= 0) {
            return false;
        }

        $processAlive = posix_kill($pid, 0);
        $fp = @fsockopen('127.0.0.1', self::MOCK_SERVER_PORT, $errno, $errstr, 1);
        $portActive = $fp !== false;
        if ($fp) {
            fclose($fp);
        }

        if ($processAlive !== $portActive) {
            self::cleanupMockServer();

            return false;
        }

        return $processAlive;
    }

    /**
     * Ensure mock server is running, restart if needed.
     *
     * @throws \RuntimeException if the server fails to start
     */
    public static function ensureMockServerRunning(): void
    {
        if (! self::isMockServerRunning()) {
            self::cleanupMockServer();
            self::startMockServer();
        }
    }

    /**
     * Kill any process occupying the mock server port.
     */
    private static function killStaleProcess(): void
    {
        $output = [];
        exec("lsof -i :" . self::MOCK_SERVER_PORT . " -t 2>/dev/null", $output);

        foreach ($output as $pid) {
            $pid = (int) trim($pid);
            if ($pid > 0) {
                posix_kill($pid, SIGTERM);
            }
        }

        if (! empty($output)) {
            usleep(300000);
        }
    }

    /**
     * Cleanup mock server state files without killing the process.
     */
    private static function cleanupMockServer(): void
    {
        @unlink(self::MOCK_SERVER_PID_PATH);
    }

    /**
     * Wait for a server to become available on the given port.
     */
    private static function waitForServer(int $port, int $timeoutSeconds): bool
    {
        $start = time();

        while ((time() - $start) < $timeoutSeconds) {
            $fp = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
            if ($fp) {
                fclose($fp);

                return true;
            }
            usleep(100000);
        }

        return false;
    }
}
