<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Whitelist alamat IP opsional untuk endpoint callback SSO.
 * Bila config('sso.ip_whitelist') kosong, semua IP diizinkan.
 */
class SsoIpWhitelist
{
    public function handle(Request $request, Closure $next): Response
    {
        $whitelist = config('sso.ip_whitelist', []);

        if (empty($whitelist)) {
            return $next($request);
        }

        if (in_array($request->ip(), $whitelist, true)) {
            return $next($request);
        }

        return $this->forbidden();
    }

    protected function forbidden(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'code' => 'CALLBACK_UNAUTHORIZED',
            'message' => 'Autentikasi gagal.',
        ], 403);
    }
}
