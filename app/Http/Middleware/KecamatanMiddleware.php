<?php

namespace App\Http\Middleware;

use App\Models\Config;
use Closure;
use Illuminate\Http\Request;

class KecamatanMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     *
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // abort jika kecamatan tidak ada di list config.
        abort_unless(
            in_array($kodeKecamatan = $request->route('kodeKecamatan'), Config::get()->pluck('kode_kecamatan')?->toArray()),
            404,
            'Kecamatan tidak ditemukan, pastikan kecamatan tersebut sudah ditambahkan di OpenSID Gabungan!'
        );

        // set session kecamatan
        session()->put(
            'kecamatan',
            Config::where('kode_kecamatan', $kodeKecamatan)->first()
        );

        return $next($request);
    }
}
