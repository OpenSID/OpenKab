<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\CakupanPenggunaService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Penegakan hak akses wilayah dan sektor DataSpasial untuk seluruh grup route `dataspasial` (issue #1320).
 *
 * Aturan (default bebas; pembatasan hanya bila diatur di form pengguna):
 * - Sektor dibatasi: sektor yang diminta (`sektor` atau `sektor_id`) harus termasuk hak akses; di luar itu → 403.
 *   Pengguna yang dibatasi tidak boleh membuat sektor baru (`sektor_nama` tanpa `sektor`) → 403.
 * - Wilayah dibatasi: parameter `kode_kabupaten`, `kode_kecamatan`, `kode_desa` dipaksa sesuai hak akses
 *   sehingga permintaan tidak bisa keluar dari wilayah pengguna.
 *
 * Penyaringan dropdown dan baris hasil dilakukan di controller/layanan masing-masing.
 */
class AksesDataSpasialMiddleware
{
    public function __construct(
        protected CakupanPenggunaService $cakupan,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user === null) {
            return $next($request);
        }

        $uid = (int) $user->id;

        $sektorAkses = $this->cakupan->sektorAkses($uid);
        if ($sektorAkses !== null && ! $this->sektorDiperbolehkan($request, $sektorAkses)) {
            return $this->tolak($request);
        }

        $wilayah = $this->cakupan->wilayahAkses($uid);
        if ($wilayah !== null) {
            $request->merge($this->cakupan->filterDasbor($wilayah));
        }

        return $next($request);
    }

    /**
     * @param  array<int, string>  $sektorAkses
     */
    private function sektorDiperbolehkan(Request $request, array $sektorAkses): bool
    {
        // Sektor baru (nama tanpa kode) hanya boleh dibuat oleh pengguna tanpa pembatasan sektor.
        if ($request->filled('sektor_nama') && ! $request->filled('sektor')) {
            return false;
        }

        if ($request->filled('sektor')) {
            $kode = (string) $request->input('sektor');
            if (! in_array($kode, $sektorAkses, true)) {
                return false;
            }
        }

        if ($request->filled('sektor_id') && ! $request->filled('sektor')) {
            $kode = $this->cakupan->kodeSektorDariId((int) $request->input('sektor_id'));
            if ($kode === null || ! in_array($kode, $sektorAkses, true)) {
                return false;
            }
        }

        return true;
    }

    private function tolak(Request $request): Response
    {
        $pesan = 'Anda tidak memiliki akses ke sektor ini.';

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $pesan], 403);
        }

        abort(403, $pesan);
    }
}
