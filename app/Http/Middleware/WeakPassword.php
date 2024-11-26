<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class WeakPassword
{
    protected $except = ['password.change'];

    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $weakPassword = session('weak_password');
        if ($weakPassword && ! in_array(Route::currentRouteName(), $this->except)) {
            // return redirect(route('password.change'));
        }

        return $next($request);
    }
}
