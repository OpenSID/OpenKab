<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Http\Middleware\GlobalRateLimiter;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Max attempt login throttle.
     *
     * @var int
     */
    public const MAX_ATTEMPT = 5;

    /**
     * Decay in second if failed attempt,
     * default is one hour.
     *
     * @var int
     */
    public const DECAY_SECOND = 600;

    /**
     * @var \App\Http\Middleware\GlobalRateLimiter
     */
    protected $globalRateLimiter;

    /**
     * Create a new controller instance.
     */
    public function __construct(GlobalRateLimiter $globalRateLimiter)
    {
        $this->globalRateLimiter = $globalRateLimiter;
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {
        $credential = $request->input('credential');
        
        // Find user to check lockout status
        $user = User::where('email', $credential)
            ->orWhere('username', $credential)
            ->first();

        // Check if account is locked
        if ($user && $user->isLocked()) {
            $remainingSeconds = $user->getLockoutRemainingSeconds();
            $minutes = ceil($remainingSeconds / 60);

            return response()->json([
                'message' => "AKUN TERKUNCI. Terlalu banyak gagal login. Coba lagi dalam {$minutes} menit.",
                'locked' => true,
                'retry_after' => $remainingSeconds,
            ], Response::HTTP_FORBIDDEN);
        }

        // Check rate limiter with enhanced key (IP + User-Agent)
        if (RateLimiter::tooManyAttempts($this->throttleKey(), static::MAX_ATTEMPT)) {
            event(new Lockout($request));

            $seconds = RateLimiter::availableIn($this->throttleKey());

            return response()->json([
                'message' => 'TERLALU BANYAK PERCobaAN. Silakan tunggu ' . ceil($seconds / 60) . ' menit sebelum mencoba lagi.',
                'retry_after' => $seconds,
            ], Response::HTTP_TOO_MANY_REQUESTS);
        }

        if (! Auth::attempt($request->only('email', 'password'))) {
            // Record failed attempt with progressive delay and account lockout
            $result = ['delay' => 0, 'locked' => false, 'attempts' => 0];
            
            if ($user) {
                $result = $user->recordFailedLogin();
            }

            RateLimiter::hit($this->throttleKey(), static::DECAY_SECOND);

            $response = [
                'message' => 'Kredensial tidak valid',
                'attempts_remaining' => $result['remaining'] ?? null,
            ];

            // Add progressive delay information
            if ($result['delay'] > 0) {
                $response['progressive_delay'] = $result['delay'];
                $response['message'] = "Kredensial tidak valid. Percobaan gagal ke-{$result['attempts']}. Delay: {$result['delay']} detik.";
            }

            // Add lockout warning
            if ($result['locked']) {
                $response['message'] = "AKUN TERKUNCI. Terlalu banyak gagal login ({$result['attempts']} kali).";
                $response['locked'] = true;
                $response['lockout_expires_in'] = $result['lockout_expires_in'] ?? 900;
            } elseif ($result['remaining'] === 0) {
                $response['message'] = "PERINGATAN: Akun akan terkunci setelah {$result['attempts']} kali gagal login.";
            }

            return response()->json($response, Response::HTTP_UNAUTHORIZED);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        // Reset failed login attempts on successful login
        $user->resetFailedLogins();
        
        // Clear rate limiter on successful login
        RateLimiter::clear($this->throttleKey());

        // Delete existing tokens
        $user->tokens->each(function ($token, $key) {
            $token->delete();
        });

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()
            ->json([
                'message' => 'Login Success',
                'access_token' => $token,
                'token_type' => 'Bearer',
            ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function logOut(Request $request)
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'Berhasil Log Out',
        ], Response::HTTP_OK);
    }

    /**
     * Get the rate limiting throttle key for the request.
     * 
     * Combines credential (email/username), IP address, and User-Agent
     * to prevent bypass via VPN/IP rotation alone.
     *
     * @return string
     */
    protected function throttleKey()
    {
        $credential = Str::lower(request('credential', ''));
        $ip = request()->ip();
        $userAgent = hash('xxh64', request()->userAgent() ?? 'unknown');
        
        return "{$credential}|{$ip}|{$userAgent}";
    }

    public function token()
    {
        $user = User::whereUsername('synchronize')->first();
        $token = $user->createToken('auth_token', ['synchronize-opendk-create'])->plainTextToken;

        return response()->json(['message' => 'Token Synchronize', 'access_token' => $token, 'token_type' => 'Bearer']);
    }
}
