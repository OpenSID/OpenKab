<?php

namespace App\Http\Controllers;

use App\Http\Requests\TwoFactorEnableRequest;
use App\Http\Requests\TwoFactorVerifyRequest;
use App\Services\TwoFactorService;
use App\Services\OtpService;
use App\Http\Middleware\GlobalRateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class TwoFactorController extends Controller
{
    protected $twoFactorService;
    protected $otpService;
    protected $globalRateLimiter;

    public function __construct(
        TwoFactorService $twoFactorService,
        OtpService $otpService,
        GlobalRateLimiter $globalRateLimiter
    ) {
        $this->twoFactorService = $twoFactorService;
        $this->otpService = $otpService;
        $this->globalRateLimiter = $globalRateLimiter;
    }    

     public function activate()
    {
        $user = Auth::user();
        $otpConfig = [
            'expires_minutes' => config('app.otp_token_expires_minutes', 5),
            'resend_seconds' => config('app.otp_resend_decay_seconds', 30),
            'length' => config('app.otp_length', 6),
        ];                
        
        return view('admin.pengaturan.2fa.activation-form', compact('user', 'otpConfig'));
    }

    /**
     * Proses aktivasi 2FA
     */
    public function enable(TwoFactorEnableRequest $request)
    {
        $userId = Auth::id();

        // Rate limiting untuk setup dengan enhanced key (IP + User-Agent + User ID)
        $key = $this->get2faSetupRateLimitKey($request, $userId);
        $maxAttempts = config('app.2fa_setup_max_attempts', 3);
        $decaySeconds = config('app.2fa_setup_decay_seconds', 300);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.'
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);
        $identifier = $request->channel === 'email' ? Auth::user()->email : Auth::user()->telegram_chat_id;
        // Simpan konfigurasi sementara di session
        $request->session()->put('temp_2fa_config', [
            'channel' => $request->channel,
            'identifier' => $identifier,
        ]);

        // Kirim OTP untuk verifikasi
        $result = $this->otpService->generateAndSend(
            Auth::id(),
            $request->channel,
            $identifier
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Kode verifikasi telah dikirim untuk aktivasi 2FA'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 400);
    }
    /**
     * Verifikasi dan konfirmasi aktivasi 2FA
     */
    public function verifyEnable(TwoFactorVerifyRequest $request)
    {
        $userId = Auth::id();

        // Rate limiting untuk verifikasi dengan enhanced key
        $key = $this->get2faVerifyRateLimitKey($request, $userId);
        $maxAttempts = config('app.2fa_verify_max_attempts', 5);
        $decaySeconds = config('app.2fa_verify_decay_seconds', 300);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan verifikasi. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.'
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);

        $tempConfig = $request->session()->get('temp_2fa_config');

        if (!$tempConfig) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi aktivasi tidak ditemukan. Silakan mulai dari awal.'
            ], 400);
        }

        $result = $this->otpService->verify(Auth::id(), $request->code);

        if ($result['success']) {
            // Aktivasi 2FA berhasil
            $this->twoFactorService->enableTwoFactor(Auth::user(), $tempConfig['channel'], $tempConfig['identifier']);
            session(['2fa_verified' => true]);
            // Hapus konfigurasi sementara
            $request->session()->forget('temp_2fa_config');
            RateLimiter::clear($key);

            return response()->json([
                'success' => true,
                'message' => '2FA berhasil diaktifkan! Anda sekarang akan diminta kode verifikasi setelah login.',
                'redirect' => route('2fa.index')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 400);
    }

    /**
     * Nonaktifkan 2FA
     */
    public function disable(Request $request)
    {
        $this->twoFactorService->disableTwoFactor(Auth::user());

        return response()->json([
            'success' => true,
            'message' => '2FA berhasil dinonaktifkan'
        ]);
    }

    /**
     * Kirim ulang kode verifikasi
     */
    public function resend(Request $request)
    {
        $tempConfig = $request->session()->get('temp_2fa_config');

        if (!$tempConfig) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi aktivasi tidak ditemukan.'
            ], 400);
        }

        $userId = Auth::id();

        // Rate limiting untuk resend dengan enhanced key
        $key = $this->get2faResendRateLimitKey($request, $userId);
        $maxAttempts = config('app.2fa_resend_max_attempts', 2);
        $decaySeconds = config('app.2fa_resend_decay_seconds', 30);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'success' => false,
                'message' => 'Tunggu ' . RateLimiter::availableIn($key) . ' detik sebelum mengirim ulang.'
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);

        $result = $this->otpService->generateAndSend(
            Auth::id(),
            $tempConfig['channel'],
            $tempConfig['identifier']
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Tampilkan halaman verifikasi 2FA setelah login
     */
    public function showChallenge()
    {
        // Jika user tidak memiliki 2FA aktif, redirect ke dashboard
        if (!Auth::user()->{'2fa_enabled'}) {
            return redirect()->route('dasbor');
        }

        // Kirim OTP untuk verifikasi
        $user = Auth::user();
        $channels = $this->twoFactorService->getTwoFactorChannels($user);
        $channel = $channels[0] ?? 'email';
        $identifier = $this->twoFactorService->getTwoFactorIdentifier($user);
        
        $this->otpService->generateAndSend($user->id, $channel, $identifier);

        return view('auth.2fa-challenge');
    }

    /**
     * Verifikasi kode 2FA setelah login
     */
    public function verifyChallenge(TwoFactorVerifyRequest $request)
    {
        $userId = Auth::id();

        // Rate limiting untuk verifikasi challenge dengan enhanced key
        $key = $this->get2faChallengeRateLimitKey($request, $userId);
        $maxAttempts = config('app.2fa_challenge_max_attempts', 5);
        $decaySeconds = config('app.2fa_challenge_decay_seconds', 300);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.'
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);

        $result = $this->otpService->verify(Auth::id(), $request->code);

        if ($result['success']) {
            // Tandai session bahwa 2FA sudah terverifikasi
            session(['2fa_verified' => true]);
            RateLimiter::clear($key);

            return response()->json([
                'success' => true,
                'message' => 'Verifikasi berhasil',
                'redirect' => session('url.intended', route('dasbor'))
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 400);
    }

    /**
     * Generate rate limit key for 2FA setup.
     * Combines IP, User-Agent, and user ID.
     */
    protected function get2faSetupRateLimitKey(Request $request, int $userId): string
    {
        $ip = $request->ip();
        $userAgent = hash('xxh64', $request->userAgent() ?? 'unknown');
        
        return "2fa-setup:{$userId}:{$ip}:{$userAgent}";
    }

    /**
     * Generate rate limit key for 2FA verification.
     * Combines IP, User-Agent, and user ID.
     */
    protected function get2faVerifyRateLimitKey(Request $request, int $userId): string
    {
        $ip = $request->ip();
        $userAgent = hash('xxh64', $request->userAgent() ?? 'unknown');
        
        return "2fa-verify:{$userId}:{$ip}:{$userAgent}";
    }

    /**
     * Generate rate limit key for 2FA resend.
     * Combines IP, User-Agent, and user ID.
     */
    protected function get2faResendRateLimitKey(Request $request, int $userId): string
    {
        $ip = $request->ip();
        $userAgent = hash('xxh64', $request->userAgent() ?? 'unknown');
        
        return "2fa-resend:{$userId}:{$ip}:{$userAgent}";
    }

    /**
     * Generate rate limit key for 2FA challenge.
     * Combines IP, User-Agent, and user ID.
     */
    protected function get2faChallengeRateLimitKey(Request $request, int $userId): string
    {
        $ip = $request->ip();
        $userAgent = hash('xxh64', $request->userAgent() ?? 'unknown');
        
        return "2fa-challenge:{$userId}:{$ip}:{$userAgent}";
    }
}