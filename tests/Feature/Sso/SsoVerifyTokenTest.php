<?php

namespace Tests\Feature\Sso;

use App\Models\Sso\OpenKabSsoToken;
use App\Models\User;
use App\Services\SsoTokenService;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\BaseTestCase;

class SsoVerifyTokenTest extends BaseTestCase
{
    protected function tokens(): SsoTokenService
    {
        return app(SsoTokenService::class);
    }

    protected function issueTokenForAdmin(?array $attrs = []): array
    {
        $admin = User::first();
        $admin->forceFill(array_merge(['active' => 1, '2fa_enabled' => true], $attrs))->save();

        return $this->tokens()->issue($admin->id, '5271010001');
    }

    protected function verifyRequest(array $body): TestResponse
    {
        $secret = (string) config('sso.callback_secret');

        return $this->postJson('/api/v1/sso/verify-token', $body, [
            'X-SSO-Callback-Key' => $secret,
            'X-SSO-Callback-Timestamp' => (string) time(),
            'X-SSO-Callback-Signature' => hash_hmac('sha256', json_encode($body), $secret),
        ]);
    }

    protected function validBody(string $token): array
    {
        return [
            'token' => $token,
            'callback_nonce' => (string) Str::uuid(),
        ];
    }

    #[Test]
    public function token_valid_diverifikasi_dan_dikonsumsi_sekali_pakai()
    {
        $issued = $this->issueTokenForAdmin();

        $response = $this->verifyRequest($this->validBody($issued['token']));

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.desa_id', '5271010001');
        $response->assertJsonPath('data.admin_id', User::first()->id);
        $response->assertJsonPath('data.expires_at', $issued['expires_at']);
        $response->assertJsonMissingPath('data.email');

        $this->assertDatabaseHas('openkab_sso_tokens', [
            'jti' => $issued['jti'],
            'used_at' => now(),
        ]);
        $this->assertDatabaseHas('openkab_sso_logs', [
            'status' => 'success',
            'token_fingerprint' => $this->tokens()->fingerprint($issued['jti']),
        ]);
    }

    #[Test]
    public function token_yang_sudah_dipakai_ditolak_replay()
    {
        $issued = $this->issueTokenForAdmin();

        $this->verifyRequest($this->validBody($issued['token']))->assertJsonPath('status', 'success');

        $response = $this->verifyRequest($this->validBody($issued['token']));

        $response->assertOk();
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('code', 'TOKEN_REPLAY');
    }

    #[Test]
    public function token_yang_kedaluwarsa_ditolak()
    {
        $issued = $this->issueTokenForAdmin();
        OpenKabSsoToken::query()->where('jti', $issued['jti'])->update(['expires_at' => now()->subMinute()]);

        $response = $this->verifyRequest($this->validBody($issued['token']));

        $response->assertOk();
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('code', 'TOKEN_EXPIRED');
    }

    #[Test]
    public function token_yang_diubah_ditolak()
    {
        $issued = $this->issueTokenForAdmin();

        $tampered = $issued['token'];
        $tampered[10] = $tampered[10] === 'a' ? 'b' : 'a';

        $response = $this->verifyRequest($this->validBody($tampered));

        $response->assertOk();
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('code', 'TOKEN_INVALID');
    }

    #[Test]
    public function token_dengan_karakter_acak_ditolak()
    {
        $response = $this->verifyRequest($this->validBody('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.abcdef.ghijkl'));

        $response->assertOk();
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('code', 'TOKEN_INVALID');
    }

    #[Test]
    public function token_ditandatangani_kunci_lain_ditolak()
    {
        $admin = User::first();
        $admin->forceFill(['active' => 1, '2fa_enabled' => true])->save();

        $privateOther = (string) File::get(base_path('tests/fixtures/sso/private-other.pem'));

        $payload = [
            'jti' => (string) Str::uuid(),
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + 300,
            'sub' => (string) $admin->id,
            'desa_id' => '5271010001',
            'iss' => config('sso.issuer'),
            'aud' => config('sso.audience'),
        ];

        $token = JWT::encode($payload, $privateOther, 'RS256');

        $response = $this->verifyRequest($this->validBody($token));

        $response->assertOk();
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('code', 'TOKEN_INVALID');
    }

    #[Test]
    public function token_beralgoritma_hs256_ditolak_anti_downgrade()
    {
        $payload = [
            'jti' => (string) Str::uuid(),
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + 300,
            'sub' => (string) User::first()->id,
            'desa_id' => '5271010001',
            'iss' => config('sso.issuer'),
            'aud' => config('sso.audience'),
        ];

        $hs256Token = JWT::encode($payload, str_repeat('x', 32), 'HS256');

        $response = $this->verifyRequest($this->validBody($hs256Token));

        $response->assertOk();
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('code', 'TOKEN_INVALID');
    }

    #[Test]
    public function callback_dengan_kunci_salah_ditolak()
    {
        $issued = $this->issueTokenForAdmin();
        $body = $this->validBody($issued['token']);

        $response = $this->postJson('/api/v1/sso/verify-token', $body, [
            'X-SSO-Callback-Key' => 'kunci-salah-yang-sangat-panjang-bgt-12345678',
            'X-SSO-Callback-Timestamp' => (string) time(),
            'X-SSO-Callback-Signature' => hash_hmac('sha256', json_encode($body), 'kunci-salah-yang-sangat-panjang-bgt-12345678'),
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('code', 'CALLBACK_UNAUTHORIZED');
    }

    #[Test]
    public function callback_tanpa_signature_ditolak()
    {
        $issued = $this->issueTokenForAdmin();
        $secret = (string) config('sso.callback_secret');

        $response = $this->postJson('/api/v1/sso/verify-token', $this->validBody($issued['token']), [
            'X-SSO-Callback-Key' => $secret,
            'X-SSO-Callback-Timestamp' => (string) time(),
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('code', 'CALLBACK_UNAUTHORIZED');
    }

    #[Test]
    public function callback_dengan_timestamp_kedaluwarsa_ditolak()
    {
        $issued = $this->issueTokenForAdmin();
        $body = $this->validBody($issued['token']);
        $secret = (string) config('sso.callback_secret');

        $response = $this->postJson('/api/v1/sso/verify-token', $body, [
            'X-SSO-Callback-Key' => $secret,
            'X-SSO-Callback-Timestamp' => (string) (time() - 3600),
            'X-SSO-Callback-Signature' => hash_hmac('sha256', json_encode($body), $secret),
        ]);

        $response->assertStatus(401);
        $response->assertJsonPath('code', 'CALLBACK_UNAUTHORIZED');
    }

    #[Test]
    public function user_dengan_state_tidak_layak_ditolak()
    {
        $this->issueTokenForAdmin(['active' => 0]);
        $issued = $this->tokens()->issue(User::first()->id, '5271010001');

        $response = $this->verifyRequest($this->validBody($issued['token']));

        $response->assertOk();
        $response->assertJsonPath('status', 'error');
        $response->assertJsonPath('code', 'USER_UNAUTHORIZED');
    }

    #[Test]
    public function respons_tidak_membocorkan_data_pribadi()
    {
        $issued = $this->issueTokenForAdmin();
        $admin = User::first();

        $response = $this->verifyRequest($this->validBody($issued['token']));

        $content = $response->getContent();
        $this->assertStringNotContainsString($admin->email, $content);
        if ($admin->phone) {
            $this->assertStringNotContainsString($admin->phone, $content);
        }
        $this->assertStringNotContainsString('password', $content);
        $this->assertStringNotContainsString('2fa_identifier', $content);
    }
}
