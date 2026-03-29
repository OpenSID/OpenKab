<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

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

        // Revoke all existing tokens for this user
        $user->tokens()->delete();
        $user->refreshTokens()->delete();

        // Create new access token with metadata
        $newToken = $user->createToken('auth_token');
        
        // Update token with metadata (IP, user agent, expiration)
        $tokenModel = PersonalAccessToken::find($newToken->accessToken->id);
        $tokenModel->forceFill([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expires_at' => now()->addMinutes(config('sanctum.expiration')),
        ]);
        $tokenModel->save();

        // Create refresh token
        $refreshToken = RefreshToken::create([
            'user_id' => $user->id,
            'refresh_token' => Str::random(100),
            'access_token_id' => $tokenModel->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'expires_at' => now()->addDays(config('auth.refresh_token_lifetime_days', 30)),
        ]);

        $token = $newToken->plainTextToken;
        RateLimiter::clear($this->throttleKey());
        // Reset failed login attempts on successful login
        $user->resetFailedLogins();                        

        return response()
            ->json([
                'message' => 'Login Success',
                'access_token' => $token,
                'refresh_token' => $refreshToken->refresh_token,
                'token_type' => 'Bearer',
                'expires_in' => config('sanctum.expiration') * 60, // in seconds
                'refresh_expires_in' => config('auth.refresh_token_lifetime', 2592000), // 30 days in seconds
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
        $newToken = $user->createToken('auth_token', ['synchronize-opendk-create']);
        
        // Update token with metadata using forceFill
        $tokenModel = PersonalAccessToken::find($newToken->accessToken->id);
        $tokenModel->forceFill([
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'expires_at' => now()->addMinutes(config('sanctum.expiration')),
        ]);
        $tokenModel->save();

        return response()->json([
            'message' => 'Token Synchronize',
            'access_token' => $newToken->plainTextToken,
            'token_type' => 'Bearer',
            'expires_in' => config('sanctum.expiration') * 60,
        ]);
    }
}
