<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPasswordExpiry
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Skip for API requests - handle separately
        if ($request->is('api/*')) {
            return $next($request);
        }

        // Check if user needs to reset password
        if ($user->requiresPasswordReset()) {
            // Allow access to password reset routes and logout
            if ($request->is('password-reset/*', 'user/reset-password', 'logout', 'change-password/*')) {
                return $next($request);
            }

            // Store intended destination
            session(['intended_url' => url()->current()]);

            // Redirect to password reset page
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'Password Anda telah expired atau perlu direset. Silakan reset password untuk melanjutkan.',
                    'requires_password_reset' => true,
                ], 403);
            }

            return redirect()->route('password.reset.form')
                ->with('warning', 'Password Anda telah expired atau perlu direset demi keamanan. Silakan buat password baru.');
        }

        return $next($request);
    }
}
