<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Services\CaptchaService;
use App\Services\OtpService;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $decayMinutes = 3;
    protected $maxAttempts = 5;

    protected $otpService;
    protected $twoFactorService;
    protected $username;

    protected $viewLoginForm = 'auth.login';

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = AppServiceProvider::HOME;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        OtpService $otpService,
        TwoFactorService $twoFactorService
    ) {
        $this->middleware('guest')->except('logout');
        $this->otpService = $otpService;
        $this->twoFactorService = $twoFactorService;
        $this->username = $this->findUsername();        
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    public function findUsername()
    {
        $login = request()->input('login');
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$fieldType => $login]);
        return $fieldType;
    }

    /**
     * Get username property.
     *
     * @return string
     */
    public function username()
    {
        return $this->username;
    }

    /**
     * Check if user account is locked before attempting login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function checkAccountLockout(Request $request)
    {
        $login = $request->input('login');
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $user = User::where($fieldType, $login)->first();
        
        if ($user && $user->isLocked()) {
            $remainingSeconds = $user->getLockoutRemainingSeconds();
            $minutes = ceil($remainingSeconds / 60);

            throw ValidationException::withMessages([
                $this->username() => "AKUN TERKUNCI. Terlalu banyak gagal login. Coba lagi dalam {$minutes} menit.",
            ]);
        }

        return $user;
    }

    /**
     * Record failed login attempt with account lockout.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    protected function recordFailedLoginAttempt(Request $request)
    {
        $login = $request->input('login');
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $decayMinutes = config('rate-limiter.decay_minutes', 5);
        $user = User::where($fieldType, $login)->first();

        // Increment rate limiter for captcha regardless of user existence
        $key = $this->getThrottleKey($request);
        RateLimiter::hit($key, $decayMinutes * 60); // 5 minutes decay
        
        if ($user) {
            
            $result = $user->recordFailedLogin();
            
            if ($result['locked']) {
                $minutes = ceil($result['lockout_expires_in'] / 60);
                $message = "AKUN TERKUNCI. Terlalu banyak gagal login ({$result['attempts']} kali). Coba lagi dalam {$minutes} menit.";
            } elseif ($result['remaining'] === 0) {
                $message = "PERINGATAN: Akun akan terkunci setelah {$result['attempts']} kali gagal login.";
            } else {
                $message = "Kredensial tidak valid. Percobaan gagal ke-{$result['attempts']}. Delay: {$result['delay']} detik.";
            }

            throw ValidationException::withMessages([
                $this->username() => $message,
            ]);
        }

        // If user not found, still increment login attempts for rate limiting
        $this->incrementLoginAttempts($request);
    }

    /**
     * Override to add account lockout check and password validation.
     */
    protected function attemptLogin(Request $request)
    {
        $this->checkAccountLockout($request);

        $successLogin = $this->guard()->attempt(
            $this->credentials($request),
            $request->boolean('remember')
        );

        if ($successLogin) {
            try {
                $request->validate(['password' => [
                    'required',
                    Password::min(8)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                        ->uncompromised(),
                ]]);
                session(['weak_password' => false]);
            } catch (ValidationException $th) {
                session(['weak_password' => true]);
                return redirect(route('password.change'))->with('success-login', 'Ganti password dengan yang lebih kuat');
            }
        } else {
            $this->recordFailedLoginAttempt($request);
        }

        return $successLogin;
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);

        $user = $this->guard()->user();
        if ($user) {
            // Don't clear rate limiter immediately on successful login
            // This allows captcha to still show if there were previous failed attempts
            // We'll let it expire naturally based on the decay time (5 minutes)
            // RateLimiter::clear($this->throttleKey());
            
            // Reset user failed login attempts
            $user->resetFailedLogins();
        }

        if ($this->twoFactorService->hasTwoFactorEnabled($user)) {
            session()->forget('2fa_verified');
            return redirect()->route('2fa.challenge');
        }

        if (session('weak_password')) {
            return redirect(route('password.change'))->with('success-login', 'Ganti password dengan yang lebih kuat');
        }

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        $captchaView = null;
        $shouldShowCaptcha = $this->shouldShowCaptcha();        
        if($shouldShowCaptcha){
            $captchaConfig = $this->getCaptchaConfig();
            $captchaView = $captchaConfig['type'] == 'builtin' ? 'auth.captcha' : 'auth.google-captcha';
        }
        $captchaConfig = $this->getCaptchaConfig();
        
        return view($this->viewLoginForm, compact('captchaView', 'shouldShowCaptcha'));
    }        

    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function validateLogin(Request $request)
    {
        $rules = [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ];

        if ($this->shouldShowCaptcha()) {
            $config = $this->getCaptchaConfig();
            
            if ($config['type'] === 'builtin') {
                $rules['captcha'] = 'required|captcha';
            } elseif ($config['type'] === 'google') {
                // Check if reCAPTCHA v3 keys are configured
                if (empty($config['google_site_key']) || empty($config['google_secret_key'])) {
                    throw ValidationException::withMessages([
                        $this->username() => 'Konfigurasi reCAPTCHA v3 tidak lengkap. Silakan hubungi administrator.',
                    ]);
                }
                
                $rules['g-recaptcha-response'] = 'required|string|recaptchav3:login,0.5';
            }
        }

        $customMessages = [
            'captcha.required' => 'Kode captcha diperlukan.',
            'captcha.captcha' => 'Kode captcha tidak sesuai.',
            'g-recaptcha-response' => [
                'recaptchav3' => 'Captcha error message',
            ],
        ];


        $request->validate($rules, $customMessages);
    }

    /**
     * Get the throttle key for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function getThrottleKey(Request $request): string
    {
        $ip = $request->ip();
        $userAgent = hash('xxh64', $request->userAgent() ?? 'unknown');

        return "captcha:{$ip}:{$userAgent}";
    }

    /**
     * Get captcha configuration from database
     *
     * @return array
     */
    protected function getCaptchaConfig(): array
    {
        return (new CaptchaService)->getCaptchaConfig();
    }

    /**
     * Check if captcha should be shown based on failed attempts
     *
     * @param  \Illuminate\Http\Request|null  $request
     * @return bool
     */
    protected function shouldShowCaptcha(?Request $request = null): bool
    {
        $config = $this->getCaptchaConfig();
        
        if (!$config['enabled']) {
            return false;
        }

        $request = $request ?: request();
        $key = $this->getThrottleKey($request);
        $attempts = RateLimiter::attempts($key);        
        return $attempts >= $config['threshold'];
    }

    /**
     * Check if user account is locked by user ID.
     *
     * @param  int  $userId
     * @return array|null
     */
    protected function checkUserLockoutById($userId)
    {
        $user = User::find($userId);
        
        if ($user && $user->isLocked()) {
            $remainingSeconds = $user->getLockoutRemainingSeconds();
            $minutes = ceil($remainingSeconds / 60);

            return [
                'locked' => true,
                'message' => "AKUN TERKUNCI. Terlalu banyak gagal login. Coba lagi dalam {$minutes} menit.",
                'retry_after' => $remainingSeconds,
            ];
        }

        return null;
    }

    /**
     * Handle failed login attempt with progressive delay and lockout.
     *
     * @param  \App\Models\User  $user
     * @return array
     */
    protected function handleFailedLoginAttempt($user)
    {
        if (!$user) {
            return [
                'success' => false,
                'message' => 'User tidak ditemukan'
            ];
        }

        $lockoutResult = $user->recordFailedLogin();

        $response = [
            'success' => false,
            'message' => 'Kredensial tidak valid',
            'attempts_remaining' => $lockoutResult['remaining'] ?? null,
        ];

        // Add progressive delay information
        if ($lockoutResult['delay'] > 0) {
            $response['progressive_delay'] = $lockoutResult['delay'];
            $response['message'] = "Kredensial tidak valid. Percobaan gagal ke-{$lockoutResult['attempts']}. Delay: {$lockoutResult['delay']} detik.";
        }

        // Add lockout warning
        if ($lockoutResult['locked']) {
            $response['message'] = "AKUN TERKUNCI. Terlalu banyak gagal login ({$lockoutResult['attempts']} kali).";
            $response['locked'] = true;
            $response['lockout_expires_in'] = $lockoutResult['lockout_expires_in'] ?? 900;
        }

        return $response;
    }

    /**
     * Generate rate limit key for OTP operations.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $operation
     * @param  int|null  $userId
     * @return string
     */
    protected function getOtpRateLimitKey(Request $request, $operation = 'login', $userId = null)
    {
        $ip = $request->ip();
        $userAgent = hash('xxh64', $request->userAgent() ?? 'unknown');
        $identifier = $request->input('identifier', 'unknown');
        
        if ($userId) {
            return "otp:{$operation}:{$userId}:{$ip}:{$userAgent}";
        }
        
        return "otp:{$operation}:{$identifier}:{$ip}:{$userAgent}";
    }
}
