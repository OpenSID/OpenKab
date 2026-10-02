<?php

namespace App\Services;

use App\Exceptions\SsoConfigurationException;
use Illuminate\Support\Facades\File;

/**
 * Manajemen kunci tanda tangan RS256.
 *
 * Private key hanya berada di sisi OpenKab (env/file konfigurasi server);
 * public key didistribusikan ke instalasi OpenSID untuk verifikasi. Mendukung
 * beberapa public key (rotasi transisi) untuk memvalidasi token yang masih
 * beredar selama masa peralihan kunci.
 */
class SsoKeyManager
{
    public const ALG = 'RS256';

    public const MIN_BITS = 2048;

    /**
     * Private key PEM (untuk menandatangani token).
     */
    public function privateKey(): string
    {
        $key = $this->load('sso.signing_private_key', 'sso.signing_private_key_file');

        if ($key === '') {
            throw new SsoConfigurationException('SSO_SIGNING_PRIVATE_KEY belum dikonfigurasi.');
        }

        return $this->assertPrivate($key);
    }

    /**
     * Public key PEM yang sedang aktif (untuk verifikasi & self-check).
     */
    public function publicKey(): string
    {
        $key = $this->load('sso.signing_public_key', 'sso.signing_public_key_file');

        if ($key === '') {
            throw new SsoConfigurationException('SSO_SIGNING_PUBLIC_KEY belum dikonfigurasi.');
        }

        return $this->assertPublic($key);
    }

    /**
     * Daftar public key PEM yang diterima verifikasi (kunci aktif + kunci lama).
     *
     * @return array<int, string>
     */
    public function publicKeys(): array
    {
        $keys = [$this->publicKey()];

        foreach ((array) config('sso.signing_public_keys_file', []) as $file) {
            if ($file !== '') {
                $keys[] = $this->assertPublic($this->readFile($file));
            }
        }

        return $keys;
    }

    /**
     * Muat material kunci dari nilai langsung env atau file PEM.
     */
    protected function load(string $valueKey, string $fileKey): string
    {
        $value = (string) config($valueKey, '');

        if ($value !== '') {
            return $value;
        }

        $file = (string) config($fileKey, '');

        if ($file === '') {
            return '';
        }

        return $this->readFile($file);
    }

    protected function readFile(string $path): string
    {
        $full = str_starts_with($path, '/') || preg_match('#^[A-Za-z]:[\\\\/]#', $path)
            ? $path
            : base_path($path);

        if (! File::exists($full)) {
            throw new SsoConfigurationException(sprintf('File kunci SSO tidak ditemukan: %s', $path));
        }

        return (string) File::get($full);
    }

    protected function assertPrivate(string $pem): string
    {
        $key = openssl_pkey_get_private($pem);

        if ($key === false) {
            throw new SsoConfigurationException('Private key SSO tidak valid.');
        }

        $details = openssl_pkey_get_details($key);

        if (! $details || ($details['bits'] ?? 0) < self::MIN_BITS) {
            throw new SsoConfigurationException(sprintf('Private key SSO wajib RSA minimal %d bit.', self::MIN_BITS));
        }

        return $pem;
    }

    protected function assertPublic(string $pem): string
    {
        $key = openssl_pkey_get_public($pem);

        if ($key === false) {
            throw new SsoConfigurationException('Public key SSO tidak valid.');
        }

        $details = openssl_pkey_get_details($key);

        if (! $details || ($details['bits'] ?? 0) < self::MIN_BITS) {
            throw new SsoConfigurationException(sprintf('Public key SSO wajib RSA minimal %d bit.', self::MIN_BITS));
        }

        return $pem;
    }
}
