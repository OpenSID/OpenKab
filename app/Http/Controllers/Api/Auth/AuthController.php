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
     * @throws ValidationException
     */
    public function login(Request $request)
    {
        if (RateLimiter::tooManyAttempts($this->throttleKey(), static::MAX_ATTEMPT)) {
            event(new Lockout($request));

            $seconds = RateLimiter::availableIn($this->throttleKey());

            return response()->json([
                'message' => 'USER TELAH DIBLOKIR KARENA GAGAL LOGIN '.static::MAX_ATTEMPT.' KALI SILAKAN COBA KEMBALI DALAM 10 MENIT',
            ], Response::HTTP_FORBIDDEN);
        }

        if (! Auth::attempt($request->only('email', 'password'))) {
            RateLimiter::hit($this->throttleKey(), static::DECAY_SECOND);

            return response()->json([
                'message' => 'Invalid login details',
            ], Response::HTTP_UNAUTHORIZED);
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
     * @return string
     */
    protected function throttleKey()
    {
        return Str::lower(request('credential')).'|'.request()->ip();
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
