<?php

namespace Tests\Feature\Sso;

use App\Models\User;
use App\Services\SsoKeyManager;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\BaseTestCase;

class SsoGenerateSessionTest extends BaseTestCase
{
    /**
     * Fake API database gabungan (sumber field website desa).
     *
     * Http::fake() mengakumulasi callback dan yang terdaftar lebih dulu menang,
     * sehingga perilaku di-drive lewat cache (dibaca saat request berjalan).
     */
    protected function fakeDesaApi(?string $website = 'http://opensid.test', bool $fails = false): void
    {
        Cache::flush();

        Cache::put('test:gabungan:website', $website, 60);
        Cache::put('test:gabungan:fails', $fails, 60);

        Http::fake(function () {
            if (Cache::get('test:gabungan:fails', false)) {
                return Http::response([], 500);
            }

            return Http::response([
                'data' => [[
                    'attributes' => [
                        'website' => (string) Cache::get('test:gabungan:website', ''),
                    ],
                ]],
                'meta' => ['pagination' => ['total' => 1]],
            ], 200);
        });
    }

    protected function adminWith2fa(): User
    {
        $this->fakeDesaApi();

        $user = User::first();
        $user->forceFill([
            '2fa_enabled' => true,
            'active' => 1,
            'kode_kabupaten' => null,
        ])->save();

        $fresh = $user->fresh();
        $this->actingAsAdmin($fresh);

        session(['2fa_verified' => true]);

        return $fresh;
    }

    #[Test]
    public function admin_berhak_dengan_2fa_mendapat_token_sso()
    {
        $user = $this->adminWith2fa();

        $response = $this->postJson(route('sso.generate'), [
            'desa_id' => '3201012001',
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => 'success',
            'token_method' => 'post_param',
        ]);
        $response->assertJsonStructure([
            'redirect_url',
            'token',
            'expires_at',
            'retry_after',
        ]);
        $response->assertJsonPath('redirect_url', 'http://opensid.test/admin/sso-login');

        $this->assertDatabaseHas('openkab_sso_tokens', [
            'admin_id' => $user->id,
            'desa_id' => '3201012001',
            'used_at' => null,
        ]);

        $this->assertDatabaseHas('openkab_sso_logs', [
            'admin_id' => $user->id,
            'desa_id' => '3201012001',
            'status' => 'success',
        ]);
    }

    #[Test]
    public function desa_id_format_tidak_valid_ditolak()
    {
        $this->adminWith2fa();

        $response = $this->postJson(route('sso.generate'), [
            'desa_id' => 'abc',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('desa_id');
    }

    #[Test]
    public function pengguna_belum_login_diarahkan_ke_halaman_login()
    {
        Auth::logout();

        $this->post(route('sso.generate'), ['desa_id' => '3201012001'])
            ->assertRedirect(route('login'));
    }

    #[Test]
    public function token_yang_diterbitkan_berlaku_lima_menit()
    {
        $this->adminWith2fa();

        $response = $this->postJson(route('sso.generate'), [
            'desa_id' => '3201012001',
        ]);

        $response->assertOk();
        $expiresAt = $response->json('expires_at');
        $this->assertGreaterThanOrEqual(now()->timestamp + 290, $expiresAt);
        $this->assertLessThanOrEqual(now()->timestamp + 310, $expiresAt);
    }

    #[Test]
    public function token_ditandatangani_rs256_dan_terverifikasi_dengan_public_key()
    {
        $this->adminWith2fa();

        $response = $this->postJson(route('sso.generate'), [
            'desa_id' => '3201012001',
        ]);

        $response->assertOk();
        $headers = JWT::jsonDecode(JWT::urlsafeB64Decode(explode('.', $response->json('token'))[0]));

        $this->assertSame('RS256', $headers->alg);

        $payload = JWT::decode(
            $response->json('token'),
            new Key(app(SsoKeyManager::class)->publicKey(), SsoKeyManager::ALG)
        );

        $this->assertSame('3201012001', $payload->desa_id);
        $this->assertSame(config('sso.issuer'), $payload->iss);
        $this->assertSame(config('sso.audience'), $payload->aud);
    }

    #[Test]
    public function desa_tanpa_website_ditolak_dengan_pesan_generik_dan_dicatat()
    {
        $user = $this->adminWith2fa();
        $this->fakeDesaApi('');

        $response = $this->postJson(route('sso.generate'), [
            'desa_id' => '3201012001',
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'status' => 'error',
            'code' => 'CONFIGURATION_ERROR',
        ]);
        $this->assertDatabaseHas('openkab_sso_logs', [
            'admin_id' => $user->id,
            'desa_id' => '3201012001',
            'status' => 'failed',
            'reason_if_failed' => 'unknown',
        ]);
    }

    #[Test]
    public function api_gabungan_gagal_ditolak_dengan_pesan_generik_dan_dicatat()
    {
        $user = $this->adminWith2fa();
        $this->fakeDesaApi(fails: true);

        $response = $this->postJson(route('sso.generate'), [
            'desa_id' => '3201012001',
        ]);

        $response->assertStatus(500);
        $response->assertJson([
            'status' => 'error',
            'code' => 'CONFIGURATION_ERROR',
        ]);
        $this->assertDatabaseHas('openkab_sso_logs', [
            'admin_id' => $user->id,
            'desa_id' => '3201012001',
            'status' => 'failed',
        ]);
    }
}
