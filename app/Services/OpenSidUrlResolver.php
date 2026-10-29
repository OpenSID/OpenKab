<?php

namespace App\Services;

use App\Exceptions\SsoConfigurationException;
use Illuminate\Support\Facades\Cache;

/**
 * Resolusi URL panel admin OpenSID untuk sebuah desa.
 *
 * Base URL diambil server-side dari field `attributes.website` desa pada API
 * database gabungan (/api/v1/wilayah/penduduk). Hasil resolusi di-cache singkat
 * agar tidak memanggil API eksternal setiap klik (SC-001).
 */
class OpenSidUrlResolver
{
    /**
     * Durasi cache resolusi base URL (detik).
     */
    protected const CACHE_TTL = 300;

    public function __construct(
        protected PendudukApiService $desaApi,
    ) {}

    /**
     * Base URL instalasi OpenSID untuk desa (tanpa trailing slash).
     *
     * Melempar SsoConfigurationException bila website kosong/tidak valid atau
     * API gabungan tidak mengembalikan data desa.
     */
    public function resolveBaseUrl(string $desaId): string
    {
        $base = Cache::remember($this->cacheKey($desaId), self::CACHE_TTL, function () use ($desaId) {
            $desa = $this->desaApi->desaSummary([
                'filter[kode_desa]' => $desaId,
                'page[size]' => 1,
            ])->first();

            if (! $desa || empty($desa->website)) {
                throw new SsoConfigurationException('URL website desa belum diisi.');
            }

            return rtrim((string) $desa->website, '/');
        });

        $scheme = parse_url($base, PHP_URL_SCHEME);
        $isLocalEnv = app()->environment(['development', 'local', 'testing']);

        if (! in_array($scheme, ['http', 'https'], true)) {
            throw new SsoConfigurationException('URL OpenSID tidak valid.');
        }

        if ($scheme === 'http' && ! $isLocalEnv) {
            throw new SsoConfigurationException('URL OpenSID wajib menggunakan HTTPS di luar lingkungan lokal.');
        }

        return $base;
    }

    /**
     * URL endpoint login SSO di OpenSID untuk desa.
     */
    public function resolveAdminLoginUrl(string $desaId): string
    {
        $path = (string) config('sso.endpoint.sso_login', 'admin/sso-login');

        return $this->resolveBaseUrl($desaId).'/'.ltrim($path, '/');
    }

    /**
     * Kunci cache per desa.
     */
    protected function cacheKey(string $desaId): string
    {
        return 'sso:opensid:base_url:'.$desaId;
    }
}
