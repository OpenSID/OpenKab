<?php

namespace App\Http\Middleware;

use App\Services\ConfigApiService;
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
        $kodeKabupaten = $request->route('kodeKabupaten');
        $semuaKode = collect((new ConfigApiService)->kabupaten())->pluck('kode_kabupaten')->toArray();

        abort_unless(
            in_array($kodeKabupaten, $semuaKode),
            404,
            'Kabupaten tidak ditemukan, pastikan kabupaten tersebut sudah ditambahkan di OpenSID Gabungan!'
        );

        // set session kabupaten
        session()->put(
            'kabupaten',
            (new ConfigApiService)->kabupatenByKode($kodeKabupaten)

        );

        return $next($request);
    }
}
