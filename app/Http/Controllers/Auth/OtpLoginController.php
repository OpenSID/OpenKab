<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\OtpLoginRequest;
use App\Http\Requests\OtpVerifyRequest;
use App\Models\User;
use App\Services\OtpService;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class OtpLoginController extends Controller
{
    protected $otpService;
    protected $twoFactorService;

    public function __construct(
        OtpService $otpService,
        TwoFactorService $twoFactorService
    ) {
        $this->middleware('guest')->except('logout');
        $this->otpService = $otpService;
        $this->twoFactorService = $twoFactorService;
    }

    /**
     * Tampilkan form OTP login
     */
    public function showLoginForm()
    {
        return view('auth.otp-login');
    }

    /**
     * Kirim OTP untuk login
     */
    public function sendOtp(OtpLoginRequest $request)
    {
        $identifier = $request->identifier;
        
        // Find user to check lockout status
        $user = User::where('otp_enabled', true)
            ->where(function($query) use ($identifier) {
                $query->where('otp_identifier', $identifier)
                    ->orWhere('email', $identifier)
                    ->orWhere('username', $identifier);
            })
            ->first();

        // Check if account is locked
        if ($user && $user->isLocked()) {
            $remainingSeconds = $user->getLockoutRemainingSeconds();
            $minutes = ceil($remainingSeconds / 60);

            return response()->json([
                'success' => false,
                'message' => "AKUN TERKUNCI. Terlalu banyak gagal login. Coba lagi dalam {$minutes} menit.",
                'locked' => true,
                'retry_after' => $remainingSeconds,
            ], 429);
        }

        // Rate limiting with enhanced key (IP + User-Agent + identifier)
        $key = $this->getOtpLoginRateLimitKey($request);
        $maxAttempts = config('app.otp_verify_max_attempts', 5);
        $decaySeconds = config('app.otp_verify_decay_seconds', 300);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.'
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan atau OTP tidak aktif'
            ], 404);
        }

        // Tentukan channel dan identifier
        $channels = $user->getOtpChannels();
        $channel = $channels[0] ?? 'email'; // Ambil channel pertama

        $identifier = $user->otp_identifier;

        $result = $this->otpService->generateAndSend($user->id, $channel, $identifier);

        if ($result['success']) {
            // Simpan user ID di session untuk verifikasi
            $request->session()->put('otp_login_user_id', $user->id);
            $request->session()->put('otp_login_channel', $channel);
        }

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Verifikasi OTP dan login
     */
    public function verifyOtp(OtpVerifyRequest $request)
    {
        $userId = $request->session()->get('otp_login_user_id');
        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak ditemukan. Silakan mulai dari awal.'
            ], 400);
        }

        $user = User::find($userId);

        // Check if account is locked
        if ($user && $user->isLocked()) {
            $remainingSeconds = $user->getLockoutRemainingSeconds();
            $minutes = ceil($remainingSeconds / 60);

            return response()->json([
                'success' => false,
                'message' => "AKUN TERKUNCI. Terlalu banyak gagal login. Coba lagi dalam {$minutes} menit.",
                'locked' => true,
                'retry_after' => $remainingSeconds,
            ], 429);
        }

        // Rate limiting untuk verifikasi dengan enhanced key
        $key = $this->getOtpVerifyRateLimitKey($request, $userId);
        $maxAttempts = config('app.otp_verify_max_attempts', 5);
        $decaySeconds = config('app.otp_verify_decay_seconds', 300);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'success' => false,
                'message' => 'Terlalu banyak percobaan verifikasi. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.'
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);

        $result = $this->otpService->verify($userId, $request->otp);

        if ($result['success']) {
            // Login user
            Auth::login($user, true);

            // Reset failed login attempts on successful OTP verification
            $user->resetFailedLogins();

            // Clear session and rate limiter
            $request->session()->forget(['otp_login_user_id', 'otp_login_channel']);
            RateLimiter::clear($key);

            // Check if user has 2FA enabled
            if ($this->twoFactorService->hasTwoFactorEnabled($user)) {
                // Clear 2FA verification session to require new verification
                session()->forget('2fa_verified');

                return response()->json([
                    'success' => true,
                    'message' => 'Login berhasil. Silakan verifikasi 2FA',
                    'redirect' => route('2fa.challenge')
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil',
                'redirect' => \App\Providers\RouteServiceProvider::HOME
            ]);
        }

        // Record failed attempt if user exists
        if ($user) {
            $lockoutResult = $user->recordFailedLogin();

            $response = [
                'success' => false,
                'message' => $result['message'],
                'attempts_remaining' => $lockoutResult['remaining'] ?? null,
            ];

            // Add progressive delay information
            if ($lockoutResult['delay'] > 0) {
                $response['progressive_delay'] = $lockoutResult['delay'];
                $response['message'] = "Kode OTP salah. Percobaan gagal ke-{$lockoutResult['attempts']}. Delay: {$lockoutResult['delay']} detik.";
            }

            // Add lockout warning
            if ($lockoutResult['locked']) {
                $response['message'] = "AKUN TERKUNCI. Terlalu banyak gagal verifikasi ({$lockoutResult['attempts']} kali).";
                $response['locked'] = true;
                $response['lockout_expires_in'] = $lockoutResult['lockout_expires_in'] ?? 900;
            }

            return response()->json($response, 400);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message']
        ], 400);
    }

    /**
     * Kirim ulang OTP
     */
    public function resendOtp(Request $request)
    {
        $userId = $request->session()->get('otp_login_user_id');
        $channel = $request->session()->get('otp_login_channel');

        if (!$userId || !$channel) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak ditemukan.'
            ], 400);
        }

        $user = User::find($userId);

        // Check if account is locked
        if ($user && $user->isLocked()) {
            $remainingSeconds = $user->getLockoutRemainingSeconds();
            $minutes = ceil($remainingSeconds / 60);

            return response()->json([
                'success' => false,
                'message' => "AKUN TERKUNCI. Terlalu banyak gagal login. Coba lagi dalam {$minutes} menit.",
                'locked' => true,
                'retry_after' => $remainingSeconds,
            ], 429);
        }

        // Rate limiting untuk resend dengan enhanced key
        $key = $this->getOtpResendRateLimitKey($request, $userId);
        $maxAttempts = config('app.otp_resend_max_attempts', 2);
        $decaySeconds = config('app.otp_resend_decay_seconds', 30);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'success' => false,
                'message' => 'Tunggu ' . RateLimiter::availableIn($key) . ' detik sebelum mengirim ulang.'
            ], 429);
        }

        RateLimiter::hit($key, 60);

        $identifier = $user->otp_identifier;

        $result = $this->otpService->generateAndSend($userId, $channel, $identifier);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Generate rate limit key for OTP login send.
     * Combines IP, User-Agent, and identifier to prevent bypass.
     */
    protected function getOtpLoginRateLimitKey(Request $request): string
    {
        $ip = $request->ip();
        $userAgent = hash('xxh64', $request->userAgent() ?? 'unknown');
        $identifier = hash('xxh64', $request->identifier ?? 'unknown');
        
        return "otp-login:{$ip}:{$userAgent}:{$identifier}";
    }

    /**
     * Generate rate limit key for OTP verification.
     * Combines IP, User-Agent, and user ID.
     */
    protected function getOtpVerifyRateLimitKey(Request $request, int $userId): string
    {
        $ip = $request->ip();
        $userAgent = hash('xxh64', $request->userAgent() ?? 'unknown');
        
        return "otp-verify-login:{$userId}:{$ip}:{$userAgent}";
    }

    /**
     * Generate rate limit key for OTP resend.
     * Combines IP, User-Agent, and user ID.
     */
    protected function getOtpResendRateLimitKey(Request $request, int $userId): string
    {
        $ip = $request->ip();
        $userAgent = hash('xxh64', $request->userAgent() ?? 'unknown');
        
        return "otp-resend-login:{$userId}:{$ip}:{$userAgent}";
    }
}
