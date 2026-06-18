<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TwoFactorService;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\Attributes\Test;
use Tests\BaseTestCase;
use Mockery;

class TwoFactorControllerTest extends BaseTestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected $twoFactorService;
    protected $otpService;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->startSession();
        
        // Create a user for testing
        $this->user = User::factory()->create([
            '2fa_enabled' => false,
            '2fa_channel' => null,
            '2fa_identifier' => null,
            'email' => 'test@example.com',
            'telegram_chat_id' => '123456789'
        ]);
        
        // Authenticate the test user
        $this->actingAs($this->user);
        
        $this->twoFactorService = Mockery::mock(TwoFactorService::class);
        $this->otpService = Mockery::mock(OtpService::class);
        
        $this->app->instance(TwoFactorService::class, $this->twoFactorService);
        $this->app->instance(OtpService::class, $this->otpService);
    }

    #[Test]
    public function it_shows_2fa_activation_page()
    {
        $response = $this->get(route('2fa.activate'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.pengaturan.2fa.activation-form');
        $response->assertViewHas('user', $this->user);
    }

    #[Test]
    public function it_can_enable_2fa_with_email()
    {
        $this->otpService
            ->shouldReceive('generateAndSend')
            ->once()
            ->with($this->user->id, 'email', $this->user->email)
            ->andReturn([
                'success' => true,
                'message' => 'Kode Token berhasil dikirim',
                'channel' => 'email'
            ]);

        $response = $this->postJson(route('2fa.enable'), [
            'channel' => 'email'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Kode verifikasi telah dikirim untuk aktivasi 2FA'
        ]);

        $this->assertArrayHasKey('temp_2fa_config', session()->all());
        $this->assertEquals('email', session('temp_2fa_config.channel'));
        $this->assertEquals($this->user->email, session('temp_2fa_config.identifier'));
    }

    #[Test]
    public function it_can_enable_2fa_with_telegram()
    {
        $this->otpService
            ->shouldReceive('generateAndSend')
            ->once()
            ->with($this->user->id, 'telegram', $this->user->telegram_chat_id)
            ->andReturn([
                'success' => true,
                'message' => 'Kode Token berhasil dikirim',
                'channel' => 'telegram'
            ]);

        $response = $this->postJson(route('2fa.enable'), [
            'channel' => 'telegram'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Kode verifikasi telah dikirim untuk aktivasi 2FA'
        ]);

        $this->assertArrayHasKey('temp_2fa_config', session()->all());
        $this->assertEquals('telegram', session('temp_2fa_config.channel'));
        $this->assertEquals($this->user->telegram_chat_id, session('temp_2fa_config.identifier'));
    }

    #[Test]
    public function it_handles_2fa_enable_failure()
    {
        $this->otpService
            ->shouldReceive('generateAndSend')
            ->once()
            ->with($this->user->id, 'email', $this->user->email)
            ->andReturn([
                'success' => false,
                'message' => 'Failed to send OTP'
            ]);

        $response = $this->postJson(route('2fa.enable'), [
            'channel' => 'email'
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Failed to send OTP'
        ]);
    }

    #[Test]
    public function it_enforces_rate_limiting_on_2fa_enable()
    {
        // Hit rate limit with new key format (includes IP and User-Agent)
        $key = '2fa-setup:' . $this->user->id . ':' . $this->app['request']->ip() . ':' . hash('xxh64', $this->app['request']->userAgent() ?? 'unknown');

        $maxAttempts = config('app.2fa_setup_max_attempts', 3);
        for ($i = 0; $i < $maxAttempts; $i++) {
            RateLimiter::hit($key);
        }

        $response = $this->postJson(route('2fa.enable'), [
            'channel' => 'email'
        ]);

        // After max attempts, account is locked (403)
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'locked' => true
        ]);
    }

    #[Test]
    public function it_can_verify_and_enable_2fa()
    {
        session(['temp_2fa_config' => [
            'channel' => 'email',
            'identifier' => 'test@example.com'
        ]]);

        $this->otpService
            ->shouldReceive('verify')
            ->once()
            ->with($this->user->id, '123456')
            ->andReturn([
                'success' => true,
                'message' => 'Kode Token berhasil diverifikasi'
            ]);

        $this->twoFactorService
            ->shouldReceive('enableTwoFactor')
            ->once()
            ->with($this->user, 'email', 'test@example.com')
            ->andReturn(true);

        $response = $this->actingAs($this->user)
            ->postJson(route('2fa.verify'), [
                'code' => '123456'
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => '2FA berhasil diaktifkan! Anda sekarang akan diminta kode verifikasi setelah login.',
            'redirect' => route('2fa.index')
        ]);

        $this->assertTrue(session('2fa_verified'));
        $this->assertArrayNotHasKey('temp_2fa_config', session()->all());
    }

    #[Test]
    public function it_rejects_2fa_verification_without_temp_config()
    {
        $response = $this->postJson(route('2fa.verify'), [
            'code' => '123456'
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Sesi aktivasi tidak ditemukan. Silakan mulai dari awal.'
        ]);
    }

    #[Test]
    public function it_handles_2fa_verification_failure()
    {
        session(['temp_2fa_config' => [
            'channel' => 'email',
            'identifier' => 'test@example.com'
        ]]);

        $this->otpService
            ->shouldReceive('verify')
            ->once()
            ->with($this->user->id, '123456')
            ->andReturn([
                'success' => false,
                'message' => 'Invalid OTP'
            ]);

        $response = $this->postJson(route('2fa.verify'), [
            'code' => '123456'
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertJsonPath('message', 'Kode tidak valid. Percobaan gagal ke-1. Delay: 2 detik.');
        $response->assertJsonPath('progressive_delay', 2);
    }

    #[Test]
    public function it_enforces_rate_limiting_on_2fa_verification()
    {
        session(['temp_2fa_config' => [
            'channel' => 'email',
            'identifier' => 'test@example.com'
        ]]);

        // Hit rate limit with new key format (includes IP and User-Agent)
        $key = '2fa-verify:' . $this->user->id . ':' . $this->app['request']->ip() . ':' . hash('xxh64', $this->app['request']->userAgent() ?? 'unknown');

        $maxAttempts = config('app.2fa_verify_max_attempts', 5);
        for ($i = 0; $i < $maxAttempts; $i++) {
            RateLimiter::hit($key);
        }

        $response = $this->postJson(route('2fa.verify'), [
            'code' => '123456'
        ]);

        // After max attempts, account is locked (403)
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'locked' => true
        ]);
    }

    #[Test]
    public function it_can_disable_2fa()
    {
        $this->twoFactorService
            ->shouldReceive('disableTwoFactor')
            ->once()
            ->with($this->user)
            ->andReturn(true);

        $response = $this->postJson(route('2fa.disable'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => '2FA berhasil dinonaktifkan'
        ]);
    }

    #[Test]
    public function it_can_resend_2fa_verification_code()
    {
        session(['temp_2fa_config' => [
            'channel' => 'email',
            'identifier' => $this->user->email
        ]]);

        $this->otpService
            ->shouldReceive('generateAndSend')
            ->once()
            ->with($this->user->id, 'email', $this->user->email)
            ->andReturn([
                'success' => true,
                'message' => 'Kode Token berhasil dikirim',
                'channel' => 'email'
            ]);

        $response = $this->postJson(route('2fa.resend'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Kode Token berhasil dikirim',
            'channel' => 'email'
        ]);
    }

    #[Test]
    public function it_rejects_resend_without_temp_config()
    {
        $response = $this->postJson(route('2fa.resend'));

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Sesi aktivasi tidak ditemukan.'
        ]);
    }

    #[Test]
    public function it_enforces_rate_limiting_on_2fa_resend()
    {
        session(['temp_2fa_config' => [
            'channel' => 'email',
            'identifier' => $this->user->email
        ]]);

        // Hit rate limit with new key format (includes IP and User-Agent)
        $key = '2fa-resend:' . $this->user->id . ':' . $this->app['request']->ip() . ':' . hash('xxh64', $this->app['request']->userAgent() ?? 'unknown');
        
        for ($i = 0; $i < 2; $i++) {
            RateLimiter::hit($key);
        }

        $response = $this->postJson(route('2fa.resend'));

        $response->assertStatus(429);
        $response->assertJson([
            'success' => false
        ]);
    }

    #[Test]
    public function it_shows_2fa_challenge_page()
    {
        $this->user->update([
            '2fa_enabled' => true,
            '2fa_channel' => json_encode(['email']),
            '2fa_identifier' => 'test@example.com'
        ]);

        $this->twoFactorService
            ->shouldReceive('getTwoFactorChannels')
            ->once()
            ->with($this->user)
            ->andReturn(['email']);

        $this->twoFactorService
            ->shouldReceive('getTwoFactorIdentifier')
            ->once()
            ->with($this->user)
            ->andReturn('test@example.com');

        $this->otpService
            ->shouldReceive('generateAndSend')
            ->once()
            ->with($this->user->id, 'email', 'test@example.com')
            ->andReturn([
                'success' => true,
                'message' => 'Kode Token berhasil dikirim'
            ]);

        $response = $this->get(route('2fa.challenge'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.2fa-challenge');
    }

    #[Test]
    public function it_redirects_to_dashboard_if_2fa_not_enabled()
    {
        $this->user->update(['2fa_enabled' => false]);

        $response = $this->actingAs($this->user)
            ->get(route('2fa.challenge'));

        $response->assertRedirect(route('dasbor'));
    }

    #[Test]
    public function it_can_verify_2fa_challenge()
    {
        $this->user->update([
            '2fa_enabled' => true,
            '2fa_channel' => json_encode(['email']),
            '2fa_identifier' => 'test@example.com'
        ]);

        $this->otpService
            ->shouldReceive('verify')
            ->once()
            ->with($this->user->id, '123456')
            ->andReturn([
                'success' => true,
                'message' => 'Kode Token berhasil diverifikasi'
            ]);

        session(['url.intended' => route('dasbor')]);

        $response = $this->postJson(route('2fa.challenge.verify'), [
            'code' => '123456'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Verifikasi berhasil',
            'redirect' => route('dasbor')
        ]);

        $this->assertTrue(session('2fa_verified'));
    }

    #[Test]
    public function it_handles_2fa_challenge_verification_failure()
    {
        $this->user->update([
            '2fa_enabled' => true,
            '2fa_channel' => json_encode(['email']),
            '2fa_identifier' => 'test@example.com'
        ]);

        $this->otpService
            ->shouldReceive('verify')
            ->once()
            ->with($this->user->id, '123456')
            ->andReturn([
                'success' => false,
                'message' => 'Invalid OTP'
            ]);

        $response = $this->postJson(route('2fa.challenge.verify'), [
            'code' => '123456'
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
        ]);
        $response->assertJsonPath('message', 'Kode tidak valid. Percobaan gagal ke-1. Delay: 2 detik.');
        $response->assertJsonPath('progressive_delay', 2);
    }

    #[Test]
    public function it_enforces_rate_limiting_on_2fa_challenge()
    {
        $this->user->update([
            '2fa_enabled' => true,
            '2fa_channel' => json_encode(['email']),
            '2fa_identifier' => 'test@example.com'
        ]);

        // Hit rate limit with new key format (includes IP and User-Agent)
        $key = '2fa-challenge:' . $this->user->id . ':' . $this->app['request']->ip() . ':' . hash('xxh64', $this->app['request']->userAgent() ?? 'unknown');

        $maxAttempts = config('app.2fa_challenge_max_attempts', 5);
        for ($i = 0; $i < $maxAttempts; $i++) {
            RateLimiter::hit($key);
        }

        $response = $this->postJson(route('2fa.challenge.verify'), [
            'code' => '123456'
        ]);

        // After max attempts, account is locked (403)
        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'locked' => true
        ]);
    }

    protected function tearDown(): void
    {
        RateLimiter::clear('2fa-setup:' . $this->user->id);
        RateLimiter::clear('2fa-verify:' . $this->user->id);
        RateLimiter::clear('2fa-resend:' . $this->user->id);
        RateLimiter::clear('2fa-challenge:' . $this->user->id);
        
        parent::tearDown();
    }
}