<?php

namespace App\Services;

use App\Exceptions\SsoConfigurationException;
use App\Models\Sso\DesaSsoConfig;

/**
 * Resolusi URL panel admin OpenSID untuk sebuah desa.
 *
 * Urutan: baris desa_sso_configs (enabled) → fallback config('sso.opensid_base_url').
 */
class OpenSidUrlResolver
{
    /**
     * Base URL instalasi OpenSID untuk desa (tanpa trailing slash).
     */
    public function resolveBaseUrl(string $desaId): string
    {
        $config = DesaSsoConfig::query()
            ->where('desa_id', $desaId)
            ->enabled()
            ->first();

        $base = rtrim((string) ($config?->opensid_url ?: config('sso.opensid_base_url')), '/');

        if ($base === '') {
            throw new SsoConfigurationException('URL OpenSID untuk desa ini belum dikonfigurasi.');
        }

        $scheme = parse_url($base, PHP_URL_SCHEME);
        $isLocalEnv = app()->environment(['local', 'testing']);

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
}
