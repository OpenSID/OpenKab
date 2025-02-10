<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class LoginController extends Controller
{
    protected $decayMinutes = 3;

    protected $maxAttempts = 5;
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Login username to be used by the controller.
     *
     * @var string
     */
    protected $username;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');

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
     * Attempt to log the user into the application.
     *
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        $successLogin = $this->guard()->attempt(
            $this->credentials($request), $request->boolean('remember')
        );

        if ($successLogin) {
            $user = $this->guard()->user();
            $cacheToken = Cache::get('user_token_'.$user->id);

            $generateToken = false;
            if (! $cacheToken) {
                $generateToken = true;
            } else {
                $token = PersonalAccessToken::findToken($cacheToken);
                if (! $token) {
                    $generateToken = true;
                }
            }

            if ($generateToken) {
                // Generate token
                Cache::forget('user_token_'.$user->id);
                $token = $this->guard()->user()->createToken('auth-token-api')->plainTextToken;
                // Store token in cache

                Cache::rememberForever('user_token_'.$user->id, function () use ($token) {
                    return $token;
                });
            }

            try {
                $request->validate(['password' => ['required', Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(),
                ],
                ]);
                session(['weak_password' => false]);
            } catch (ValidationException  $th) {
                session(['weak_password' => true]);

                return redirect(route('password.change'))->with('success-login', 'Ganti password dengan yang lebih kuat');
            }
        }

        Artisan::call('optimize:clear');
        Artisan::call('config:cache');

        return $successLogin;
    }
}
