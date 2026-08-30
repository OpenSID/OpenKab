<?php

namespace App\Console\Commands;

use App\Services\SsoKeyManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SsoGenerateKeysCommand extends Command
{
    protected $signature = 'sso:generate-keys
        {--bits=2048 : Ukuran kunci RSA (minimum 2048)}
        {--path=storage/sso : Direktori output file kunci}
        {--env-file=.env : File environment yang akan diisi}
        {--force : Timpa kunci yang sudah terkonfigurasi}';

    protected $description = 'Buat keypair RS256 (private + public) untuk SSO OpenSID dan isi nilainya ke file .env';

    /**
     * Key yang diwajibkan berisi path file kunci.
     */
    protected const PRIVATE_FILE_KEY = 'SSO_SIGNING_PRIVATE_KEY_FILE';

    protected const PUBLIC_FILE_KEY = 'SSO_SIGNING_PUBLIC_KEY_FILE';

    /**
     * Key nilai PEM langsung — dikosongkan agar file kunci yang dipakai.
     */
    protected const PRIVATE_KEY = 'SSO_SIGNING_PRIVATE_KEY';

    protected const PUBLIC_KEY = 'SSO_SIGNING_PUBLIC_KEY';

    public function handle(): int
    {
        $bits = $this->option('bits');
        $path = (string) $this->option('path');
        $envFile = (string) $this->option('env-file');
        $force = (bool) $this->option('force');

        if (! is_numeric($bits) || (int) $bits < SsoKeyManager::MIN_BITS) {
            $this->error(sprintf('Ukuran kunci minimal %d-bit.', SsoKeyManager::MIN_BITS));

            return self::FAILURE;
        }

        $envPath = $this->resolvePath($envFile);

        if (! File::exists($envPath)) {
            $this->error(sprintf('File env tidak ditemukan: %s', $envPath));

            return self::FAILURE;
        }

        if (! $force && $this->hasExistingKeys($envPath)) {
            $this->error('Kunci SSO sudah terkonfigurasi. Gunakan --force untuk menimpanya.');

            return self::FAILURE;
        }

        $keypair = $this->generateKeypair((int) $bits);

        if ($keypair === null) {
            $this->error('Gagal membuat keypair RSA.');

            return self::FAILURE;
        }

        $dir = $this->resolvePath($path);
        $privateFile = rtrim($path, '/').'/sso-private.pem';
        $publicFile = rtrim($path, '/').'/sso-public.pem';

        if (! File::isDirectory($dir)) {
            File::makeDirectory($dir, 0777, true);
        }

        File::put($privateFile, $keypair['private']);
        File::put($publicFile, $keypair['public']);
        chmod($this->resolvePath($privateFile), 0600);

        $this->setEnvValue($envPath, self::PRIVATE_KEY, '');
        $this->setEnvValue($envPath, self::PUBLIC_KEY, '');
        $this->setEnvValue($envPath, self::PRIVATE_FILE_KEY, $privateFile);
        $this->setEnvValue($envPath, self::PUBLIC_FILE_KEY, $publicFile);

        $this->info(sprintf('Keypair RS256 (%d-bit) berhasil dibuat.', $bits));
        $this->line(sprintf('  Private: %s (chmod 0600)', $privateFile));
        $this->line(sprintf('  Public : %s', $publicFile));
        $this->line(sprintf('Env diisi: %s', $envPath));
        $this->warn('Sebarkan public key ke setiap instalasi OpenSID secara out-of-band; private key tidak pernah dibagikan.');

        return self::SUCCESS;
    }

    /**
     * Buat keypair RSA dan kembalikan PEM private + public.
     *
     * @return array{private: string, public: string}|null
     */
    protected function generateKeypair(int $bits): ?array
    {
        $resource = openssl_pkey_new([
            'private_key_bits' => $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($resource === false) {
            return null;
        }

        if (! openssl_pkey_export($resource, $privatePem)) {
            return null;
        }

        $details = openssl_pkey_get_details($resource);

        if ($details === false || ($details['bits'] ?? 0) < SsoKeyManager::MIN_BITS) {
            return null;
        }

        return [
            'private' => $privatePem,
            'public' => (string) $details['key'],
        ];
    }

    /**
     * Cek apakah kunci (nilai langsung atau file) sudah terisi di file env.
     */
    protected function hasExistingKeys(string $envPath): bool
    {
        $content = (string) File::get($envPath);

        foreach ([self::PRIVATE_KEY, self::PRIVATE_FILE_KEY, self::PUBLIC_KEY, self::PUBLIC_FILE_KEY] as $key) {
            $value = $this->envValue($content, $key);

            if ($value !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * Baca nilai key dari konten env.
     */
    protected function envValue(string $content, string $key): string
    {
        if (preg_match('/^'.preg_quote($key, '/').'=(.*)$/m', $content, $matches)) {
            return trim($matches[1], " \t\"");
        }

        return '';
    }

    /**
     * Set nilai key di file env (update bila ada, tambah bila belum).
     */
    protected function setEnvValue(string $envPath, string $key, string $value): void
    {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES) ?: [];
        $found = false;

        foreach ($lines as &$line) {
            if (preg_match('/^'.preg_quote($key, '/').'=/', $line)) {
                $line = $key.'='.$value;
                $found = true;
            }
        }
        unset($line);

        if (! $found) {
            $lines[] = $key.'='.$value;
        }

        File::put($envPath, implode(PHP_EOL, $lines).PHP_EOL);
    }

    /**
     * Resolusi path: absolut bila diawali '/' atau drive, relatif terhadap base_path.
     */
    protected function resolvePath(string $path): string
    {
        if (str_starts_with($path, '/') || preg_match('#^[A-Za-z]:[\\\\/]#', $path)) {
            return $path;
        }

        return base_path($path);
    }
}
