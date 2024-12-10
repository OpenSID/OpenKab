<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class WebsiteEnable
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $website = Setting::where(['key' => 'website_enable'])->first()?->value ?? 0;

        if (! $website) {
            return redirect('login');
        }
        $homePage = Setting::where(['key' => 'home_page'])->first()?->value ?? 0;
        if ($homePage == 'presisi') {
            return redirect('presisi');
        }

        return $next($request);
    }
}
