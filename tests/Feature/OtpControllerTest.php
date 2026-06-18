<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OtpService;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\RateLimiter;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\BaseTestCase;

class OtpControllerTest extends BaseTestCase
{
    use DatabaseTransactions;

    protected User $user;
    protected $otpService;
    protected $twoFactorService;

    public function setUp(): void
    {
        parent::setUp();
        
        $this->startSession();
        
        // Create a user for testing
        $this->user = User::factory()->create([
            'otp_enabled' => false,
            'otp_channel' => null,
            'otp_identifier' => null,
            'email' => 'test@example.com',
            'telegram_chat_id' => '123456789'
        ]);
        
        // Authenticate the test user
        $this->actingAs($this->user);
        
        // Replace services with mocks after parent setup
        $this->otpService = Mockery::mock(OtpService::class);
        $this->twoFactorService = Mockery::mock(TwoFactorService::class);
        
        $this->app->instance(OtpService::class, $this->otpService);
        $this->app->instance(TwoFactorService::class, $this->twoFactorService);
    }

    #[Test]
    public function it_shows_otp_settings_page()
    {
        $this->twoFactorService
            ->shouldReceive('getUserTwoFactorStatus')
            ->once()
            ->with($this->user)
            ->andReturn([
                'enabled' => false,
                'channel' => null,
                'identifier' => null,
            ]);

        $response = $this->get(route('otp.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.pengaturan.otp.index');
        $response->assertViewHas('user', $this->user);
        $response->assertViewHas('twoFactorStatus');
    }

    #[Test]
    public function it_shows_otp_activation_page()
    {
        $response = $this->get(route('otp.activate'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.pengaturan.otp.activation-form');
        $response->assertViewHas('user', $this->user);
    }

    #[Test]
    public function it_can_setup_otp_with_email()
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

        $response = $this->postJson(route('otp.setup'), [
            'channel' => 'email'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Kode OTP telah dikirim untuk verifikasi aktivasi',
            'channel' => 'email'
        ]);

        $this->assertArrayHasKey('temp_otp_config', session()->all());
        $this->assertEquals('email', session('temp_otp_config.channel'));
        $this->assertEquals($this->user->email, session('temp_otp_config.identifier'));
    }

    #[Test]
    public function it_can_setup_otp_with_telegram()
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

        $response = $this->postJson(route('otp.setup'), [
            'channel' => 'telegram'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Kode OTP telah dikirim untuk verifikasi aktivasi',
            'channel' => 'telegram'
        ]);

        $this->assertArrayHasKey('temp_otp_config', session()->all());
        $this->assertEquals('telegram', session('temp_otp_config.channel'));
        $this->assertEquals($this->user->telegram_chat_id, session('temp_otp_config.identifier'));
    }

    #[Test]
    public function it_handles_otp_setup_failure()
    {
        $this->otpService
            ->shouldReceive('generateAndSend')
            ->once()
            ->with($this->user->id, 'email', $this->user->email)
            ->andReturn([
                'success' => false,
                'message' => 'Failed to send OTP'
            ]);

        $response = $this->postJson(route('otp.setup'), [
            'channel' => 'email'
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Failed to send OTP'
        ]);
    }

    #[Test]
    public function it_enforces_rate_limiting_on_otp_setup()
    {
        // Hit rate limit with new key format (includes IP and User-Agent)
        $key = 'otp-setup:' . $this->user->id . ':' . $this->app['request']->ip() . ':' . hash('xxh64', $this->app['request']->userAgent() ?? 'unknown');
        
        for ($i = 0; $i < 3; $i++) {
            RateLimiter::hit($key);
        }

        $response = $this->postJson(route('otp.setup'), [
            'channel' => 'email'
        ]);

        $response->assertStatus(429);
        $response->assertJson([
            'success' => false
        ]);
    }

    #[Test]
    public function it_can_verify_otp_activation()
    {
        session(['temp_otp_config' => [
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

        $response = $this->postJson(route('otp.verify-activation'), [
            'otp' => '123456'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'OTP berhasil diaktifkan! Anda sekarang dapat menggunakan OTP sebagai alternatif login.'
        ]);

        $this->user->refresh();
        $this->assertTrue((bool) $this->user->otp_enabled);
        $this->assertEquals(['email'], json_decode($this->user->otp_channel, true));
        $this->assertEquals('test@example.com', $this->user->otp_identifier);

        $this->assertArrayNotHasKey('temp_otp_config', session()->all());
    }

    #[Test]
    public function it_rejects_otp_verification_without_temp_config()
    {
        $response = $this->postJson(route('otp.verify-activation'), [
            'otp' => '123456'
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Sesi aktivasi tidak ditemukan. Silakan mulai dari awal.'
        ]);
    }

    #[Test]
    public function it_handles_otp_verification_failure()
    {
        session(['temp_otp_config' => [
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

        $response = $this->postJson(route('otp.verify-activation'), [
            'otp' => '123456'
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Invalid OTP'
        ]);
    }

    #[Test]
    public function it_enforces_rate_limiting_on_otp_verification()
    {
        session(['temp_otp_config' => [
            'channel' => 'email',
            'identifier' => 'test@example.com'
        ]]);

        // Hit rate limit with new key format (includes IP and User-Agent)
        $key = 'otp-verify:' . $this->user->id . ':' . $this->app['request']->ip() . ':' . hash('xxh64', $this->app['request']->userAgent() ?? 'unknown');
        
        for ($i = 0; $i < 5; $i++) {
            RateLimiter::hit($key);
        }

        $response = $this->postJson(route('otp.verify-activation'), [
            'otp' => '123456'
        ]);

        $response->assertStatus(429);
        $response->assertJson([
            'success' => false
        ]);
    }

    #[Test]
    public function it_can_disable_otp()
    {
        $this->user->update([
            'otp_enabled' => true,
            'otp_channel' => json_encode(['email']),
            'otp_identifier' => 'test@example.com'
        ]);

        $response = $this->postJson(route('otp.disable'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'OTP berhasil dinonaktifkan'
        ]);

        $this->user->refresh();
        $this->assertFalse((bool) $this->user->otp_enabled);
        $this->assertNull($this->user->otp_channel);
        $this->assertNull($this->user->otp_identifier);
    }

    #[Test]
    public function it_can_resend_otp()
    {
        session(['temp_otp_config' => [
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

        $response = $this->postJson(route('otp.resend'));

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
        $response = $this->postJson(route('otp.resend'));

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'Sesi aktivasi tidak ditemukan.'
        ]);
    }

    #[Test]
    public function it_enforces_rate_limiting_on_otp_resend()
    {
        session(['temp_otp_config' => [
            'channel' => 'email',
            'identifier' => $this->user->email
        ]]);

        // Hit rate limit with new key format (includes IP and User-Agent)
        $key = 'otp-resend:' . $this->user->id . ':' . $this->app['request']->ip() . ':' . hash('xxh64', $this->app['request']->userAgent() ?? 'unknown');
        
        for ($i = 0; $i < 2; $i++) {
            RateLimiter::hit($key);
        }

        $response = $this->postJson(route('otp.resend'));

        $response->assertStatus(429);
        $response->assertJson([
            'success' => false
        ]);
    }

    #[Test]
    public function it_can_disable_2fa_from_otp_controller()
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

    protected function tearDown(): void
    {
        RateLimiter::clear('otp-setup:' . $this->user->id);
        RateLimiter::clear('otp-verify:' . $this->user->id);
        RateLimiter::clear('otp-resend:' . $this->user->id);
        
        parent::tearDown();
    }
}