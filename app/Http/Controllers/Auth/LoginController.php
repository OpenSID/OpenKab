<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\OtpService;
use App\Services\TwoFactorService;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

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
        
        $user = User::where($fieldType, $login)->first();

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

        $this->incrementLoginAttempts($request);
    }

    /**
     * Override to add account lockout check and password validation.
     */
    protected function attemptLogin(Request $request)
    {
        $this->checkAccountLockout($request);

        $successLogin = $this->guard()->attempt(
            $this->credentials($request), $request->boolean('remember')
        );

        if ($successLogin) {
            try {
                $request->validate(['password' => ['required', Password::min(8)
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
}
