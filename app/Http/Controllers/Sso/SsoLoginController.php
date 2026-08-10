<?php

namespace App\Http\Controllers\Sso;

use App\Enums\SsoStatusEnum;
use App\Exceptions\SsoConfigurationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\SsoGenerateRequest;
use App\Models\User;
use App\Services\OpenSidUrlResolver;
use App\Services\SsoAuditLogger;
use App\Services\SsoTokenService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SsoLoginController extends Controller
{
    public function __construct(
        protected SsoTokenService $tokens,
        protected SsoAuditLogger $audit,
        protected OpenSidUrlResolver $resolver,
    ) {}

    /**
     * Terbitkan token SSO sekali pakai untuk administrator yang berhak.
     */
    public function generateSession(SsoGenerateRequest $request): JsonResponse
    {
        $user = $request->user();
        $desaId = $request->validated('desa_id');

        if (! $this->isOriginAllowed($request)) {
            return $this->reject($user, $desaId, SsoStatusEnum::REASON_ORIGIN_INVALID, 'ORIGIN_INVALID', 403);
        }

        $failure = $this->checkEligibility($user, $desaId);
        if ($failure !== null) {
            return $this->reject($user, $desaId, $failure['reason'], $failure['code'], $failure['status']);
        }

        try {
            $redirectUrl = $this->resolver->resolveAdminLoginUrl($desaId);
            $issued = $this->tokens->issue($user->id, $desaId);
        } catch (SsoConfigurationException $e) {
            return $this->reject($user, $desaId, SsoStatusEnum::REASON_UNKNOWN, 'CONFIGURATION_ERROR', 500);
        }

        $this->audit->logAttempt(
            $user->id,
            $desaId,
            SsoStatusEnum::SUCCESS,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
            tokenFingerprint: $this->tokens->fingerprint($issued['jti']),
        );

        return response()->json([
            'status' => 'success',
            'redirect_url' => $redirectUrl,
            'token' => $issued['token'],
            'token_method' => 'post_param',
            'expires_at' => $issued['expires_at'],
            'retry_after' => (int) config('sso.rate_limit_max', 5),
        ]);
    }

    /**
     * Gate kelayakan berurutan, default-to-reject (fail-safe).
     *
     * @return array{reason: string, code: string, status: int}|null
     */
    protected function checkEligibility(User $user, string $desaId): ?array
    {
        if (! ($user->hasRole('administrator') || $user->isSuperAdmin())) {
            return ['reason' => SsoStatusEnum::REASON_BUKAN_ADMIN, 'code' => 'AUTH_FAILED', 'status' => 403];
        }

        if (session('2fa_verified') !== true) {
            return ['reason' => SsoStatusEnum::REASON_2FA_BELUM_VERIFIKASI, 'code' => 'AUTH_FAILED', 'status' => 403];
        }

        if (! $user->{'2fa_enabled'}) {
            return ['reason' => SsoStatusEnum::REASON_2FA_NONAKTIF, 'code' => 'AUTH_FAILED', 'status' => 403];
        }

        if (! $user->active) {
            return ['reason' => SsoStatusEnum::REASON_AKUN_NONAKTIF, 'code' => 'AUTH_FAILED', 'status' => 403];
        }

        if ($user->isLocked()) {
            return ['reason' => SsoStatusEnum::REASON_AKUN_TERKUNCI, 'code' => 'AUTH_FAILED', 'status' => 403];
        }

        if ($user->kode_kabupaten && ! Str::startsWith($desaId, (string) $user->kode_kabupaten)) {
            return ['reason' => SsoStatusEnum::REASON_DESA_INVALID, 'code' => 'VALIDATION_FAILED', 'status' => 422];
        }

        return null;
    }

    /**
     * Validasi asal permintaan (Origin/Referer) untuk mencegah CSRF/cross-site.
     */
    protected function isOriginAllowed(Request $request): bool
    {
        if (app()->environment(['local', 'testing'])) {
            return true;
        }

        $origin = $request->headers->get('Origin') ?: $request->headers->get('Referer');

        if (! $origin) {
            return false;
        }

        $appUrl = rtrim((string) config('app.url'), '/');

        return Str::startsWith($origin, $appUrl);
    }

    /**
     * Respons gagal generik + pencatatan audit.
     */
    protected function reject(User $user, string $desaId, string $reason, string $code, int $status): JsonResponse
    {
        $this->audit->logAttempt(
            $user->id,
            $desaId,
            SsoStatusEnum::FAILED,
            $reason,
            request()->ip(),
            request()->userAgent(),
        );

        return response()->json([
            'status' => 'error',
            'message' => 'Autentikasi gagal.',
            'code' => $code,
        ], $status);
    }
}
