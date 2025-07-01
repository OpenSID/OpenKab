<?php

namespace App\Http\Middleware;

use App\Models\Config;
use App\Services\ConfigApiService;
use Closure;
use Illuminate\Http\Request;

class KecamatanMiddleware
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
        // abort jika kecamatan tidak ada di list config.
        $kodeKecamatan = $request->route('kodeKecamatan');
        $semuaKode = collect((new ConfigApiService)->kecamatan())->pluck('kode_kecamatan')->toArray();

        abort_unless(
            in_array($kodeKecamatan, $semuaKode),
            404,
            'Kecamatan tidak ditemukan, pastikan kecamatan tersebut sudah ditambahkan di OpenSID Gabungan!'
        );

        $currentKecamatan = (new ConfigApiService)->kecamatanByKode($kodeKecamatan);

        // Hapus session desa jika berpindah kecamatan
        if (session()->has('kecamatan') && session('kecamatan.kode_kecamatan') !== $currentKecamatan['kode_kecamatan']) {
            session()->forget('desa');
        }

        session()->put('kecamatan', $currentKecamatan);

        return $next($request);
    }
}
