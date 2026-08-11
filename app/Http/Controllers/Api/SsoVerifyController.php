<?php

namespace App\Http\Controllers\Api;

use App\Enums\SsoStatusEnum;
use App\Exceptions\SsoConfigurationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\SsoVerifyRequest;
use App\Models\Sso\OpenKabSsoToken;
use App\Models\User;
use App\Services\SsoAuditLogger;
use App\Services\SsoTokenService;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\JsonResponse;
use UnexpectedValueException;

class SsoVerifyController extends Controller
{
    public function __construct(
        protected SsoTokenService $tokens,
        protected SsoAuditLogger $audit,
    ) {}

    /**
     * Verifikasi token yang dikirim OpenSID sebelum pembuatan sesi.
     * Dipanggil server-to-server (diotentikasi oleh SsoCallbackAuth).
     */
    public function verify(SsoVerifyRequest $request): JsonResponse
    {
        $token = (string) $request->input('token');

        try {
            $payload = $this->tokens->verify($token);
        } catch (ExpiredException $e) {
            return $this->error('TOKEN_EXPIRED');
        } catch (BeforeValidException $e) {
            return $this->error('TOKEN_INVALID');
        } catch (UnexpectedValueException|SignatureInvalidException|\DomainException $e) {
            return $this->error('TOKEN_INVALID');
        } catch (SsoConfigurationException $e) {
            return $this->error('TOKEN_INVALID');
        }

        $adminId = (int) $payload['sub'];
        $desaId = (string) $payload['desa_id'];
        $jti = (string) $payload['jti'];
        $fingerprint = $this->tokens->fingerprint($jti);

        $tokenRecord = OpenKabSsoToken::query()->where('jti', $jti)->first();

        if (! $tokenRecord) {
            return $this->error('TOKEN_INVALID', $adminId, $desaId, $fingerprint);
        }

        if ($tokenRecord->used_at !== null) {
            return $this->error('TOKEN_REPLAY', $adminId, $desaId, $fingerprint);
        }

        if ($tokenRecord->expires_at->isPast()) {
            return $this->error('TOKEN_EXPIRED', $adminId, $desaId, $fingerprint);
        }

        $admin = User::query()->find($adminId);

        if (! $admin || ! $this->adminIsEligible($admin)) {
            return $this->error('USER_UNAUTHORIZED', $adminId, $desaId, $fingerprint);
        }

        if (! OpenKabSsoToken::consumeAtomic($jti, $request->ip())) {
            return $this->error('TOKEN_REPLAY', $adminId, $desaId, $fingerprint);
        }

        $this->audit->logVerification(
            $adminId,
            $desaId,
            SsoStatusEnum::SUCCESS,
            ip: $request->ip(),
            userAgent: $request->userAgent(),
            tokenFingerprint: $fingerprint,
        );

        return response()->json([
            'status' => 'success',
            'data' => [
                'desa_id' => $desaId,
                'username' => $admin->username,
                'admin_id' => $adminId,
                'expires_at' => (int) $payload['exp'],
            ],
        ]);
    }

    /**
     * Cek ulang state administrator (double-check sebelum membuat sesi di OpenSID).
     */
    protected function adminIsEligible(User $admin): bool
    {
        if (! $admin->active) {
            return false;
        }

        if ($admin->isLocked()) {
            return false;
        }

        if (! ($admin->hasRole('administrator') || $admin->isSuperAdmin())) {
            return false;
        }

        if (! $admin->{'2fa_enabled'}) {
            return false;
        }

        return true;
    }

    /**
     * Respons gagal generik (HTTP 200, hasil di status) + pencatatan audit.
     * Token error yang tidak diketahui identitasnya tidak dicatat ke DB audit.
     */
    protected function error(string $code, ?int $adminId = null, ?string $desaId = null, ?string $fingerprint = null): JsonResponse
    {
        if ($adminId !== null && $desaId !== null) {
            $this->audit->logVerification(
                $adminId,
                $desaId,
                SsoStatusEnum::FAILED,
                strtolower($code),
                request()->ip(),
                request()->userAgent(),
                $fingerprint,
            );
        }

        return response()->json([
            'status' => 'error',
            'code' => $code,
            'message' => 'Token tidak valid.',
        ]);
    }
}
