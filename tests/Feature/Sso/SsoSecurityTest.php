<?php

namespace Tests\Feature\Sso;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\BaseTestCase;

class SsoSecurityTest extends BaseTestCase
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

    protected function loginAsAdminState(array $attrs = [], bool $twoFaVerified = true, bool $twoFaEnabled = true): User
    {
        $this->fakeDesaApi();

        $user = User::first();
        $user->forceFill(array_merge([
            'active' => 1,
            '2fa_enabled' => $twoFaEnabled,
            'kode_kabupaten' => null,
        ], $attrs))->save();

        $fresh = $user->fresh();
        $this->actingAsAdmin($fresh);

        if ($twoFaVerified) {
            session(['2fa_verified' => true]);
        } else {
            session()->forget('2fa_verified');
        }

        return $fresh;
    }

    protected function postGenerate(string $desaId = '5271010001')
    {
        return $this->postJson(route('sso.generate'), ['desa_id' => $desaId]);
    }

    #[Test]
    public function pengguna_non_admin_ditolak_dan_dicatat()
    {
        $nonAdmin = User::factory()->create(['2fa_enabled' => true]);
        $this->actingAsAdmin($nonAdmin);
        session(['2fa_verified' => true]);

        $response = $this->postGenerate();

        $response->assertStatus(403);
        $response->assertJson([
            'status' => 'error',
            'code' => 'AUTH_FAILED',
        ]);
        $this->assertDatabaseHas('openkab_sso_logs', [
            'admin_id' => $nonAdmin->id,
            'status' => 'failed',
            'reason_if_failed' => 'bukan_admin',
        ]);
    }

    #[Test]
    public function sesi_tanpa_verifikasi_2fa_ditolak()
    {
        $this->loginAsAdminState(twoFaVerified: false);

        $response = $this->postGenerate();

        $response->assertStatus(403);
        $response->assertJsonPath('code', 'AUTH_FAILED');
        $this->assertDatabaseHas('openkab_sso_logs', [
            'status' => 'failed',
            'reason_if_failed' => '2fa_belum_verifikasi',
        ]);
    }

    #[Test]
    public function user_dengan_2fa_nonaktif_ditolak()
    {
        $user = $this->loginAsAdminState(twoFaEnabled: false);

        $response = $this->postGenerate();

        $response->assertStatus(403);
        $response->assertJsonPath('code', 'AUTH_FAILED');
        $this->assertDatabaseHas('openkab_sso_logs', [
            'admin_id' => $user->id,
            'status' => 'failed',
            'reason_if_failed' => '2fa_nonaktif',
        ]);
    }

    #[Test]
    public function akun_nonaktif_ditolak()
    {
        $user = $this->loginAsAdminState(['active' => 0]);

        $response = $this->postGenerate();

        $response->assertStatus(403);
        $response->assertJsonPath('code', 'AUTH_FAILED');
        $this->assertDatabaseHas('openkab_sso_logs', [
            'admin_id' => $user->id,
            'status' => 'failed',
            'reason_if_failed' => 'akun_nonaktif',
        ]);
    }

    #[Test]
    public function akun_terkunci_ditolak()
    {
        $user = $this->loginAsAdminState([
            'locked_at' => now(),
            'lockout_expires_at' => now()->addMinutes(15),
        ]);

        $response = $this->postGenerate();

        $response->assertStatus(403);
        $response->assertJsonPath('code', 'AUTH_FAILED');
        $this->assertDatabaseHas('openkab_sso_logs', [
            'admin_id' => $user->id,
            'status' => 'failed',
            'reason_if_failed' => 'akun_terkunci',
        ]);
    }

    #[Test]
    public function desa_di_luar_scope_kabupaten_ditolak()
    {
        $user = $this->loginAsAdminState(['kode_kabupaten' => '3273']);

        $response = $this->postGenerate('5271010001');

        $response->assertStatus(422);
        $response->assertJsonPath('code', 'VALIDATION_FAILED');
        $this->assertDatabaseHas('openkab_sso_logs', [
            'admin_id' => $user->id,
            'status' => 'failed',
            'reason_if_failed' => 'desa_invalid',
        ]);
    }

    #[Test]
    public function respons_error_tidak_membocorkan_data_pribadi_atau_detail_teknis()
    {
        $user = $this->loginAsAdminState(twoFaVerified: false);

        $response = $this->postGenerate();

        $response->assertStatus(403);
        $response->assertJsonPath('message', 'Autentikasi gagal.');

        $content = $response->getContent();
        $this->assertStringNotContainsString($user->username, $content);
        $this->assertStringNotContainsString($user->email, $content);
        $this->assertStringNotContainsString('SsoTokenService', $content);
        $this->assertStringNotContainsString('SQLSTATE', $content);
    }

    #[Test]
    public function desa_tanpa_website_ditolak_dengan_pesan_generik_dan_dicatat()
    {
        $user = $this->loginAsAdminState();
        $this->fakeDesaApi('');

        $response = $this->postGenerate();

        $response->assertStatus(500);
        $response->assertJson([
            'status' => 'error',
            'code' => 'CONFIGURATION_ERROR',
        ]);
        $response->assertJsonPath('message', 'Autentikasi gagal.');
        $this->assertDatabaseHas('openkab_sso_logs', [
            'admin_id' => $user->id,
            'status' => 'failed',
            'reason_if_failed' => 'unknown',
        ]);
    }

    #[Test]
    public function rate_limit_memblokir_permintaan_berlebih()
    {
        config(['sso.rate_limit_max' => 2]);
        $this->loginAsAdminState();

        $this->postGenerate()->assertOk();
        $this->postGenerate()->assertOk();

        $response = $this->postGenerate();

        $response->assertStatus(429);
        $response->assertJson([
            'status' => 'error',
            'code' => 'RATE_LIMITED',
        ]);
        $response->assertHeader('Retry-After');

        $this->assertDatabaseHas('openkab_sso_logs', [
            'status' => 'failed',
            'reason_if_failed' => 'rate_limited',
        ]);
    }
}
