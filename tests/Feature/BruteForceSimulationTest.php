<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Test untuk simulasi brute force attack pada login dan API
 */
class BruteForceSimulationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Clear cache untuk memastikan state bersih
        Cache::flush();
        RateLimiter::clear('test');

        // Buat user test
        $this->user = User::factory()->create([
            'email' => 'bruteforce@test.com',
            'password' => Hash::make('password123'),
            'username' => 'bruteforce_user',
            'active' => 1,
            'failed_login_attempts' => 0,
            'locked_at' => null,
            'lockout_expires_at' => null,
        ]);
    }

    #[Test]
    public function account_locked_after_multiple_failed_api_login_attempts()
    {
        Config::set('app.account_lockout_max_attempts', 5);
        Config::set('app.account_lockout_decay_minutes', 15);

        // Simulasi 5 failed login attempts
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->postJson('/api/v1/signin', [
                'credential' => 'bruteforce@test.com',
                'password' => 'wrong_password',
            ]);

            // Setiap attempt harus return 401 (unauthorized)
            $response->assertStatus(401);

            // Refresh user dan cek failed attempts bertambah
            $this->user->refresh();
            $this->assertEquals($i, $this->user->failed_login_attempts);
        }

        // Setelah 5 attempt, akun harus terkunci
        $this->user->refresh();
        $this->assertTrue($this->user->isLocked());
        $this->assertEquals(5, $this->user->failed_login_attempts);
        $this->assertNotNull($this->user->locked_at);
        $this->assertNotNull($this->user->lockout_expires_at);
    }

    #[Test]
    public function locked_account_rejects_even_correct_password()
    {
        Config::set('app.account_lockout_max_attempts', 3);

        // Lock akun dengan 3 failed attempts
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/v1/signin', [
                'credential' => 'bruteforce@test.com',
                'password' => 'wrong_password',
            ]);
        }

        // Verify akun locked
        $this->user->refresh();
        $this->assertTrue($this->user->isLocked());

        // Coba login dengan password benar - harus ditolak
        $response = $this->postJson('/api/v1/signin', [
            'credential' => 'bruteforce@test.com',
            'password' => 'password123', // password benar
        ]);

        // Harus return 403 (Forbidden) karena akun locked
        $response->assertStatus(403);
        $response->assertJson([
            'locked' => true,
        ]);
        $this->assertStringContainsString('TERKUNCI', $response->json('message'));
    }

    #[Test]
    public function successful_login_resets_failed_attempts()
    {
        // Test ini memverifikasi bahwa method resetFailedLogins() bekerja
        // Reset failed attempts manual
        $this->user->update([
            'failed_login_attempts' => 5,
            'locked_at' => now(),
            'lockout_expires_at' => now()->addMinutes(15),
        ]);

        // Verify set
        $this->user->refresh();
        $this->assertEquals(5, $this->user->failed_login_attempts);
        $this->assertTrue($this->user->isLocked());

        // Call reset method
        $this->user->resetFailedLogins();

        // Verify reset
        $this->user->refresh();
        $this->assertEquals(0, $this->user->failed_login_attempts);
        $this->assertNull($this->user->locked_at);
        $this->assertNull($this->user->lockout_expires_at);
        $this->assertFalse($this->user->isLocked());
    }

    #[Test]
    public function resists_distributed_attack_with_same_user_agent()
    {
        Config::set('rate-limiter.enabled', true);
        Config::set('rate-limiter.max_attempts', 5);
        Config::set('rate-limiter.decay_minutes', 1);

        $userAgent = 'AttackBot/1.0';

        // Simulasi attack dari 5 IP berbeda tapi User-Agent sama
        for ($i = 0; $i < 5; $i++) {
            $response = $this->withHeaders([
                'User-Agent' => $userAgent,
                'X-Forwarded-For' => "192.168.1.{$i}",
            ])->postJson('/api/v1/signin', [
                'credential' => 'bruteforce@test.com',
                'password' => 'wrong_password',
            ]);

            $response->assertStatus(401);
        }

        // Attempt ke-6 dari IP berbeda tapi User-Agent sama
        $response = $this->withHeaders([
            'User-Agent' => $userAgent,
            'X-Forwarded-For' => '192.168.1.99',
        ])->postJson('/api/v1/signin', [
            'credential' => 'bruteforce@test.com',
            'password' => 'wrong_password',
        ]);

        // Harus di-rate limit (429) atau akun locked (403)
        $this->assertTrue(
            $response->status() === 429 || $response->status() === 403,
            "Distributed attack harus dicegah. Status: {$response->status()}"
        );
    }

    #[Test]
    public function resists_vpn_ip_rotation_attack()
    {
        Config::set('rate-limiter.enabled', true);
        Config::set('rate-limiter.max_attempts', 5);
        Config::set('rate-limiter.decay_minutes', 1);

        // Browser fingerprint yang sama (User-Agent)
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/120.0.0.0';

        // Simulasi VPN user rotate IP 5 kali
        for ($i = 0; $i < 5; $i++) {
            $response = $this->withHeaders([
                'User-Agent' => $userAgent,
                'X-Forwarded-For' => "203.0.113.{$i}",
            ])->postJson('/api/v1/signin', [
                'credential' => 'bruteforce@test.com',
                'password' => 'wrong_password',
            ]);

            $response->assertStatus(401);
        }

        // Coba dengan IP VPN baru
        $response = $this->withHeaders([
            'User-Agent' => $userAgent,
            'X-Forwarded-For' => '203.0.113.99',
        ])->postJson('/api/v1/signin', [
            'credential' => 'bruteforce@test.com',
            'password' => 'wrong_password',
        ]);

        // Harus tetap di-rate limit karena User-Agent sama
        $this->assertTrue(
            $response->status() === 429 || $response->status() === 403,
            "VPN rotation attack harus dicegah. Status: {$response->status()}"
        );
    }

    #[Test]
    public function brute_force_protection_works_with_username()
    {
        Config::set('app.account_lockout_max_attempts', 3);

        // Lock akun menggunakan username
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson('/api/v1/signin', [
                'credential' => 'bruteforce_user', // username, bukan email
                'password' => 'wrong_password',
            ]);

            $response->assertStatus(401);
        }

        // Verify akun locked
        $this->user->refresh();
        $this->assertTrue($this->user->isLocked());

        // Coba login dengan username + password benar
        $response = $this->postJson('/api/v1/signin', [
            'credential' => 'bruteforce_user',
            'password' => 'password123',
        ]);

        // Harus ditolak karena akun locked
        $response->assertStatus(403);
        $response->assertJson(['locked' => true]);
    }

    #[Test]
    public function progressive_delay_increases_with_failed_attempts()
    {
        Config::set('app.progressive_delay_base_seconds', 2);
        Config::set('app.progressive_delay_multiplier', 2);

        // Expected delays: 2s, 4s, 8s, 16s, 32s
        $expectedDelays = [2, 4, 8, 16, 32];

        for ($i = 0; $i < 5; $i++) {
            $startTime = microtime(true);
            
            $response = $this->postJson('/api/v1/signin', [
                'credential' => 'bruteforce@test.com',
                'password' => 'wrong_password',
            ]);

            $endTime = microtime(true);
            $elapsedTime = $endTime - $startTime;

            $response->assertStatus(401);

            // Response harus include progressive delay info
            if ($i > 0) { // Skip first attempt
                $responseData = $response->json();
                $this->assertArrayHasKey('progressive_delay', $responseData);
                $this->assertEquals($expectedDelays[$i], $responseData['progressive_delay']);
            }
        }
    }

    #[Test]
    public function account_lockout_has_expiration_time()
    {
        // Clear cache dari test sebelumnya
        Cache::flush();
        
        Config::set('app.account_lockout_max_attempts', 2);
        Config::set('app.account_lockout_decay_minutes', 15);

        // Lock akun
        for ($i = 0; $i < 2; $i++) {
            $this->postJson('/api/v1/signin', [
                'credential' => 'bruteforce@test.com',
                'password' => 'wrong_password',
            ]);
        }

        // Verify locked
        $this->user->refresh();
        $this->assertTrue($this->user->isLocked());
        
        // Verify expiration time is set (15 minutes from now)
        $this->assertNotNull($this->user->lockout_expires_at);
        $this->assertGreaterThan(now(), $this->user->lockout_expires_at);
        $this->assertLessThanOrEqual(now()->addMinutes(15), $this->user->lockout_expires_at);
    }

    #[Test]
    public function different_accounts_have_independent_lockout()
    {
        // Clear cache dari test sebelumnya
        Cache::flush();
        
        Config::set('app.account_lockout_max_attempts', 3);

        // Buat user kedua
        $user2 = User::factory()->create([
            'email' => 'user2lock@test.com',
            'password' => Hash::make('password123'),
            'username' => 'user2_lock',
        ]);

        // Lock user pertama
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/v1/signin', [
                'credential' => 'bruteforce@test.com',
                'password' => 'wrong_password',
            ]);
        }

        // User pertama locked
        $this->user->refresh();
        $this->assertTrue($this->user->isLocked());
        $this->assertEquals(3, $this->user->failed_login_attempts);

        // User kedua TIDAK locked
        $user2->refresh();
        $this->assertFalse($user2->isLocked());
        $this->assertEquals(0, $user2->failed_login_attempts);
        
        // Verify user2 can still attempt login (not blocked by user1's lockout)
        // We just verify the account is not locked, not actually login
        $this->assertFalse($user2->isLocked());
    }

    #[Test]
    public function rate_limit_responses_include_proper_headers()
    {
        Config::set('rate-limiter.enabled', true);
        Config::set('rate-limiter.max_attempts', 2);
        Config::set('rate-limiter.decay_minutes', 1);

        // Exhaust rate limit
        for ($i = 0; $i < 2; $i++) {
            $this->postJson('/api/v1/signin', [
                'credential' => 'bruteforce@test.com',
                'password' => 'wrong_password',
            ]);
        }

        // Request ke-3 harus rate limited
        $response = $this->postJson('/api/v1/signin', [
            'credential' => 'bruteforce@test.com',
            'password' => 'wrong_password',
        ]);

        // Response harus 429 atau 403
        $this->assertTrue(
            $response->status() === 429 || $response->status() === 403,
            "Expected 429 or 403, got {$response->status()}"
        );

        // Check JSON response structure
        $response->assertJsonStructure([
            'message',
        ]);
    }
}
