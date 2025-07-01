<?php

namespace App\Http\Middleware;

use App\Models\Config;
use App\Services\ConfigApiService;
use Closure;
use Illuminate\Http\Request;

class WilayahMiddleware
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
        $kodeDesa = $request->route('kodeDesa');
        $semuaKode = collect((new ConfigApiService)->desa())->pluck('kode_desa')->toArray();

        // abort jika app_key desa tidak ada di list config.
        abort_unless(
            in_array($kodeDesa, $semuaKode),
            404,
            'Desa tidak ditemukan, pastikan desa tersebut sudah ditambahkan di OpenSID Gabungan!'
        );

        // set session desa
        session()->put(
            'desa',
            (new ConfigApiService)->desaByKode($kodeDesa)
        );

        return $next($request);
    }
}
