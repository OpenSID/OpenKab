<?php

namespace App\Services;

use App\Models\Sso\OpenKabSsoLog;
use Illuminate\Support\Str;

/**
 * Pencatatan audit SSO yang immutable (append-only).
 */
class SsoAuditLogger
{
    /**
     * Catat percobaan pembuatan akses SSO (berhasil/gagal).
     */
    public function logAttempt(
        int $adminId,
        string $desaId,
        string $status,
        ?string $reason = null,
        ?string $ip = null,
        ?string $userAgent = null,
        ?string $tokenFingerprint = null,
    ): OpenKabSsoLog {
        return $this->create($adminId, $desaId, $status, $reason, $ip, $userAgent, $tokenFingerprint);
    }

    /**
     * Catat percobaan verifikasi token dari sisi OpenSID (callback).
     */
    public function logVerification(
        int $adminId,
        string $desaId,
        string $status,
        ?string $reason = null,
        ?string $ip = null,
        ?string $userAgent = null,
        ?string $tokenFingerprint = null,
    ): OpenKabSsoLog {
        return $this->create($adminId, $desaId, $status, $reason, $ip, $userAgent, $tokenFingerprint);
    }

    protected function create(
        int $adminId,
        string $desaId,
        string $status,
        ?string $reason,
        ?string $ip,
        ?string $userAgent,
        ?string $tokenFingerprint,
    ): OpenKabSsoLog {
        $request = request();

        return OpenKabSsoLog::create([
            'admin_id' => $adminId,
            'desa_id' => $desaId,
            'attempt_time' => now(),
            'status' => $status,
            'reason_if_failed' => $reason,
            'ip_address' => $ip ?? $request->ip(),
            'user_agent' => Str::limit($userAgent ?? $request->userAgent() ?? 'unknown', 255, ''),
            'token_fingerprint' => $tokenFingerprint,
        ]);
    }
}
