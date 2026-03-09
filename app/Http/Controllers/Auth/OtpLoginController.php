<?php

namespace App\Http\Controllers\Auth;

use App\Http\Requests\OtpLoginRequest;
use App\Http\Requests\OtpVerifyRequest;
use App\Models\User;
use App\Services\OtpService;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class OtpLoginController extends LoginController
{
    protected $viewLoginForm = 'auth.otp-login';

    public function __construct(
        OtpService $otpService,
        TwoFactorService $twoFactorService
    ) {
        parent::__construct($otpService, $twoFactorService);
    }

    /**
     * Validate the OTP login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateOtpLogin(Request $request)
    {
        $rules = [
            'identifier' => 'required|string',
        ];

        if ($this->shouldShowCaptcha($request)) {
            $config = $this->getCaptchaConfig();
            
            if ($config['type'] === 'builtin') {
                $rules['captcha'] = 'required|captcha';
            } elseif ($config['type'] === 'google') {
                // Check if reCAPTCHA v3 keys are configured
                if (empty($config['google_site_key']) || empty($config['google_secret_key'])) {
                    throw ValidationException::withMessages([
                        'identifier' => 'Konfigurasi reCAPTCHA v3 tidak lengkap. Silakan hubungi administrator.',
                    ]);
                }
                
                $rules['g-recaptcha-response'] = 'required|string|recaptchav3:login,0.5';
            }
        }

        $customMessages = [
            'captcha.required' => 'Kode captcha diperlukan.',
            'captcha.captcha' => 'Kode captcha tidak sesuai.',
            'g-recaptcha-response' => [
                'recaptchav3' => 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.',
            ],
        ];

        $request->validate($rules, $customMessages);
    }

    /**
     * Kirim OTP untuk login
     */
    public function sendOtp(OtpLoginRequest $request)
    {
        // Validate OTP login request including captcha
        $this->validateOtpLogin($request);
        
        $identifier = $request->identifier;
        
        // Find user to check lockout status
        $user = User::where('otp_enabled', true)
            ->where(function($query) use ($identifier) {
                $query->where('otp_identifier', $identifier)
                    ->orWhere('email', $identifier)
                    ->orWhere('username', $identifier);
            })
            ->first();

        // Check if account is locked using parent method
        $lockoutCheck = $this->checkUserLockoutById($user?->id);
        if ($lockoutCheck) {
            return response()->json($lockoutCheck, 429);
        }

        // Rate limiting using parent method
        $key = $this->getOtpRateLimitKey($request, 'login');
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
            // Track failed username attempts for captcha
            $this->trackFailedUsernameAttempt($request);
            
            // Check if we should show captcha after 2 failed attempts
            $shouldShowCaptcha = $this->shouldShowCaptchaAfterFailedAttempts($request);
            
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan atau OTP tidak aktif',
                'show_captcha' => $shouldShowCaptcha,
                'refresh_page' => $shouldShowCaptcha
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

        // Check if account is locked using parent method
        $lockoutCheck = $this->checkUserLockoutById($user?->id);
        if ($lockoutCheck) {
            return response()->json($lockoutCheck, 429);
        }

        // Rate limiting using parent method
        $key = $this->getOtpRateLimitKey($request, 'verify', $userId);
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

        // Handle failed attempt using parent method
        if ($user) {
            $failedResponse = $this->handleFailedLoginAttempt($user);
            $failedResponse['message'] = $result['message']; // Override with OTP-specific message
            
            return response()->json($failedResponse, 400);
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

        // Check if account is locked using parent method
        $lockoutCheck = $this->checkUserLockoutById($user?->id);
        if ($lockoutCheck) {
            return response()->json($lockoutCheck, 429);
        }

        // Rate limiting using parent method
        $key = $this->getOtpRateLimitKey($request, 'resend', $userId);
        $maxAttempts = config('app.otp_resend_max_attempts', 2);
        $decaySeconds = config('app.otp_resend_decay_seconds', 30);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            return response()->json([
                'success' => false,
                'message' => 'Tunggu ' . RateLimiter::availableIn($key) . ' detik sebelum mengirim ulang.'
            ], 429);
        }

        RateLimiter::hit($key, $decaySeconds);

        $identifier = $user->otp_identifier;

        $result = $this->otpService->generateAndSend($userId, $channel, $identifier);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Show the OTP login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        $captchaView = null;
        $shouldShowCaptcha = $this->shouldShowCaptcha(request());
        if($shouldShowCaptcha){
            $captchaConfig = $this->getCaptchaConfig();
            $captchaView = $captchaConfig['type'] == 'builtin' ? 'auth.captcha' : 'auth.google-captcha';
        }
        $captchaConfig = $this->getCaptchaConfig();
        
        return view($this->viewLoginForm, compact('captchaView', 'shouldShowCaptcha'));
    }
    /**
     * Track failed username attempts for captcha display
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function trackFailedUsernameAttempt(Request $request)
    {
        $key = $this->getUsernameAttemptKey($request);
        $decaySeconds = config('app.otp_username_attempt_decay', 300); // 5 minutes
        RateLimiter::hit($key, $decaySeconds);
    }

    /**
     * Get the key for tracking username attempts
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function getUsernameAttemptKey(Request $request): string
    {
        $ip = $request->ip();
        $userAgent = hash('xxh64', $request->userAgent() ?? 'unknown');
        return "otp:username_attempt:{$ip}:{$userAgent}";
    }

    /**
     * Check if captcha should be shown after failed username attempts
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldShowCaptchaAfterFailedAttempts(Request $request): bool
    {
        $config = $this->getCaptchaConfig();
        $key = $this->getUsernameAttemptKey($request);
        $attempts = RateLimiter::attempts($key);
        return $attempts >= ($config['threshold'] ?? 2); // Show captcha after 2 failed attempts
    }

    /**
     * Override parent method to also check for failed username attempts
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return bool
     */
    protected function shouldShowCaptcha(?Request $request = null): bool
    {
        // First check parent implementation
        $parentShouldShow = parent::shouldShowCaptcha($request);
        
        // Also check for failed username attempts
        $request = $request ?: request();
        $shouldShowAfterFailedAttempts = $this->shouldShowCaptchaAfterFailedAttempts($request);
        
        return $parentShouldShow || $shouldShowAfterFailedAttempts;
    }
}
