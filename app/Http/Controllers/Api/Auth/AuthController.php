<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
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

        // hapus token yang masih tersimpan
        Auth::user()->tokens->each(function ($token, $key) {
            $token->delete();
        });

        $token = $user->createToken('auth_token')->plainTextToken;
        RateLimiter::clear($this->throttleKey());

        return response()
            ->json(['message' => 'Login Success ', 'access_token' => $token, 'token_type' => 'Bearer']);
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
        $token = $user->createToken('auth_token', ['synchronize-opendk-create'])->plainTextToken;

        return response()->json(['message' => 'Token Synchronize', 'access_token' => $token, 'token_type' => 'Bearer']);
    }
}
