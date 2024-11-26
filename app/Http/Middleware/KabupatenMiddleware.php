<?php

namespace App\Http\Middleware;

use App\Models\Config;
use Closure;
use Illuminate\Http\Request;

class KabupatenMiddleware
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
        // abort jika kabupaten tidak ada di list config.
        abort_unless(
            in_array($kodeKabupaten = $request->route('kodeKabupaten'), Config::get()->pluck('kode_kabupaten')?->toArray()),
            404,
            'Kabupaten tidak ditemukan, pastikan kabupaten tersebut sudah ditambahkan di OpenSID Gabungan!'
        );

        // set session kabupaten
        session()->put(
            'kabupaten',
            Config::where('kode_kabupaten', $kodeKabupaten)->first()
        );

        return $next($request);
    }
}
