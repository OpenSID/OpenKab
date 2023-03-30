<?php

namespace App\Http\Middleware;

use App\Models\Config;
use Closure;
use Illuminate\Http\Request;

class WilayahMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // abort jika app_key desa tidak ada di list config.
        abort_unless(
            in_array($appKey = $request->route('appKey'), Config::get()->pluck('app_key')?->toArray()),
            404,
            'Desa tidak ditemukan, pastikan desa tersebut sudah ditambahkan di OpenSID Gabungan!'
        );

        // set session desa
        session()->put(
            'desa',
            Config::where('app_key', $appKey)->first()
        );

        return $next($request);
    }
}
