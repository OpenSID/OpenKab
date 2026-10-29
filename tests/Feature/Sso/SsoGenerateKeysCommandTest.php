<?php

namespace Tests\Feature\Sso;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\File;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SsoGenerateKeysCommandTest extends TestCase
{
    protected string $dir;

    protected string $envFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir().'/openkab-sso-keys-'.uniqid();
        $this->envFile = $this->dir.'/keys.env';

        File::makeDirectory($this->dir, 0777, true);
        File::put($this->envFile, "APP_ENV=testing\n");
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);

        parent::tearDown();
    }

    #[Test]
    public function command_membuat_keypair_dan_mengisi_file_env()
    {
        $this->artisan('sso:generate-keys', [
            '--path' => $this->dir,
            '--env-file' => $this->envFile,
        ])->assertSuccessful();

        $this->assertFileExists($this->dir.'/sso-private.pem');
        $this->assertFileExists($this->dir.'/sso-public.pem');

        $perms = fileperms($this->dir.'/sso-private.pem') & 0777;
        $this->assertSame(0600, $perms, 'private key wajib 0600');

        $content = File::get($this->envFile);
        $this->assertStringContainsString('SSO_SIGNING_PRIVATE_KEY_FILE='.$this->dir.'/sso-private.pem', $content);
        $this->assertStringContainsString('SSO_SIGNING_PUBLIC_KEY_FILE='.$this->dir.'/sso-public.pem', $content);
        $this->assertStringContainsString('SSO_SIGNING_PRIVATE_KEY=', $content);
        $this->assertStringContainsString('SSO_SIGNING_PUBLIC_KEY=', $content);

        $privatePem = File::get($this->dir.'/sso-private.pem');
        $publicPem = File::get($this->dir.'/sso-public.pem');

        $this->assertNotFalse(openssl_pkey_get_private($privatePem), 'private key valid');
        $this->assertNotFalse(openssl_pkey_get_public($publicPem), 'public key valid');

        $details = openssl_pkey_get_details(openssl_pkey_get_private($privatePem));
        $this->assertGreaterThanOrEqual(2048, $details['bits']);
    }

    #[Test]
    public function kunci_yang_dihasilkan_dapat_memverifikasi_jwt_rs256()
    {
        $this->artisan('sso:generate-keys', [
            '--path' => $this->dir,
            '--env-file' => $this->envFile,
        ])->assertSuccessful();

        $privatePem = File::get($this->dir.'/sso-private.pem');
        $publicPem = File::get($this->dir.'/sso-public.pem');

        $token = JWT::encode(['sub' => '1'], $privatePem, 'RS256');

        $payload = JWT::decode($token, new Key($publicPem, 'RS256'));

        $this->assertSame('1', $payload->sub);
    }

    #[Test]
    public function command_ditolak_saat_kunci_sudah_terisi_tanpa_force()
    {
        File::put($this->envFile, "SSO_SIGNING_PRIVATE_KEY_FILE=storage/sso/sso-private.pem\n");

        $this->artisan('sso:generate-keys', [
            '--path' => $this->dir,
            '--env-file' => $this->envFile,
        ])->assertExitCode(1);

        $this->assertFileDoesNotExist($this->dir.'/sso-private.pem');
    }

    #[Test]
    public function command_menimpa_ketika_diberi_force()
    {
        File::put($this->envFile, "SSO_SIGNING_PRIVATE_KEY_FILE=storage/sso/sso-private.pem\n");

        $this->artisan('sso:generate-keys', [
            '--path' => $this->dir,
            '--env-file' => $this->envFile,
            '--force' => true,
        ])->assertSuccessful();

        $this->assertFileExists($this->dir.'/sso-private.pem');
        $this->assertStringContainsString(
            'SSO_SIGNING_PRIVATE_KEY_FILE='.$this->dir.'/sso-private.pem',
            File::get($this->envFile)
        );
    }

    #[Test]
    public function command_menolak_ukuran_kunci_kurang_dari_2048()
    {
        $this->artisan('sso:generate-keys', [
            '--bits' => 1024,
            '--path' => $this->dir,
            '--env-file' => $this->envFile,
        ])->assertExitCode(1);

        $this->assertFileDoesNotExist($this->dir.'/sso-private.pem');
    }
}
