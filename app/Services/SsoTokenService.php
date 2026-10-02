<?php

namespace App\Services;

use App\Exceptions\SsoConfigurationException;
use App\Models\Sso\OpenKabSsoToken;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Str;

/**
 * Terbitkan, verifikasi, dan konsumsi token SSO (JWT RS256, sekali pakai).
 */
class SsoTokenService
{
    public function __construct(
        protected SsoKeyManager $keys,
    ) {}

    /**
     * Terbitkan token baru untuk administrator & desa target, lalu catat di
     * tabel openkab_sso_tokens untuk dukungan sekali pakai / deteksi replay.
     *
     * @return array{token: string, expires_at: int, jti: string}
     */
    public function issue(int $adminId, string $desaId): array
    {
        $ttl = $this->ttl();
        $now = time();
        $jti = (string) Str::uuid();

        $payload = [
            'jti' => $jti,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $ttl,
            'sub' => (string) $adminId,
            'desa_id' => $desaId,
            'iss' => config('sso.issuer'),
            'aud' => config('sso.audience'),
        ];

        $token = JWT::encode($payload, $this->keys->privateKey(), self::ALG());

        OpenKabSsoToken::create([
            'jti' => $jti,
            'admin_id' => $adminId,
            'desa_id' => $desaId,
            'token_fingerprint' => $this->fingerprint($jti),
            'expires_at' => now()->addSeconds($ttl),
        ]);

        return [
            'token' => $token,
            'expires_at' => $payload['exp'],
            'jti' => $jti,
        ];
    }

    /**
     * Verifikasi tanda tangan RS256, masa berlaku, issuer, audience, dan desa.
     *
     * Melempar exception Firebase berikut bila gagal (di-handle pemanggil):
     * - Firebase\JWT\ExpiredException          → token kedaluwarsa
     * - Firebase\JWT\BeforeValidException      → token belum berlaku (iat/nbf)
     * - Firebase\JWT\UnexpectedValueException  → token rusak/alg tak dikenal
     * - Firebase\JWT\SignatureInvalidException → tanda tangan tidak cocok
     * - App\Exceptions\SsoConfigurationException → key/claim tidak valid
     *
     * @param  string|null  $expectedDesaId  jika diberikan, desa target token wajib cocok
     * @return array<string, mixed> payload terverifikasi
     */
    public function verify(string $token, ?string $expectedDesaId = null): array
    {
        $previousLeeway = JWT::$leeway;
        JWT::$leeway = (int) config('sso.clock_skew_tolerance', 30);

        try {
            $payload = $this->decodeWithAnyPublicKey($token);
        } finally {
            JWT::$leeway = $previousLeeway;
        }

        $data = (array) $payload;

        if (($data['iss'] ?? null) !== config('sso.issuer')) {
            throw new SsoConfigurationException('Issuer token tidak cocok.');
        }

        if (($data['aud'] ?? null) !== config('sso.audience')) {
            throw new SsoConfigurationException('Audience token tidak cocok.');
        }

        if ($expectedDesaId !== null && ($data['desa_id'] ?? null) !== $expectedDesaId) {
            throw new SsoConfigurationException('Desa target pada token tidak cocok.');
        }

        if (empty($data['jti']) || empty($data['sub'])) {
            throw new SsoConfigurationException('Klaim token tidak lengkap.');
        }

        return $data;
    }

    /**
     * Coba verifikasi terhadap seluruh public key yang diterima (rotasi transisi).
     * Algoritma dipaksa RS256 pada Key sehingga token dengan alg lain (mis. HS256)
     * ditolak (anti downgrade).
     */
    protected function decodeWithAnyPublicKey(string $token): object
    {
        $lastSignatureError = null;

        foreach ($this->keys->publicKeys() as $publicKey) {
            try {
                return JWT::decode($token, new Key($publicKey, SsoKeyManager::ALG));
            } catch (ExpiredException|BeforeValidException $e) {
                // Tanda tangan terverifikasi untuk kunci ini; token valid secara
                // kriptografis tetapi tidak berlaku waktu → diagnosis akurat.
                throw $e;
            } catch (\Throwable $e) {
                $lastSignatureError = $e;
            }
        }

        throw $lastSignatureError instanceof \Throwable
            ? $lastSignatureError
            : new \UnexpectedValueException('Tidak ada public key untuk verifikasi.');
    }

    /**
     * Algoritma tanda tangan (RS256).
     */
    public static function ALG(): string
    {
        return SsoKeyManager::ALG;
    }

    /**
     * Masa berlaku token (detik), dibatasi oleh batas atas konfigurasi.
     */
    public function ttl(): int
    {
        $max = (int) config('sso.token_ttl_max', 600);
        $ttl = (int) config('sso.token_ttl', 300);

        return max(60, min($ttl, $max));
    }

    /**
     * Sidik jari token untuk log & lookup (hash dari jti).
     */
    public function fingerprint(string $jti): string
    {
        return hash('sha256', $jti);
    }
}
