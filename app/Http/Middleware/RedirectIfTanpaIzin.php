<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Jika pengguna tidak punya permission yang diminta, arahkan ke halaman beranda
 * (yang tidak dibatasi permission) alih-alih menampilkan 403.
 */
class RedirectIfTanpaIzin
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (! $request->user()?->can($permission)) {
            return redirect()->route('beranda');
        }

        return $next($request);
    }
}
