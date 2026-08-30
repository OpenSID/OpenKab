<?php

namespace App\Http\Middleware;

use App\Exceptions\SsoConfigurationException;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Otentikasi callback server-to-server dari OpenSID.
 *
 * Memvalidasi: X-SSO-Callback-Key (sekret bersama), X-SSO-Callback-Timestamp
 * (± toleransi selisih jam), dan X-SSO-Callback-Signature (HMAC-SHA256 dari
 * raw body). Semua perbandingan memakai hash_equals (constant-time).
 */
class SsoCallbackAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = (string) config('sso.callback_secret');

        if (strlen($secret) < 32) {
            throw new SsoConfigurationException('SSO_CALLBACK_SECRET wajib minimal 32 byte.');
        }

        $key = (string) $request->header('X-SSO-Callback-Key', '');
        $timestamp = (int) $request->header('X-SSO-Callback-Timestamp', 0);
        $signature = (string) $request->header('X-SSO-Callback-Signature', '');

        if (! hash_equals($secret, $key)) {
            return $this->unauthorized();
        }

        $tolerance = (int) config('sso.clock_skew_tolerance', 30);
        if ($timestamp === 0 || abs(time() - $timestamp) > $tolerance) {
            return $this->unauthorized();
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);
        if (! hash_equals($expected, $signature)) {
            return $this->unauthorized();
        }

        return $next($request);
    }

    protected function unauthorized(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'code' => 'CALLBACK_UNAUTHORIZED',
            'message' => 'Autentikasi gagal.',
        ], 401);
    }
}
