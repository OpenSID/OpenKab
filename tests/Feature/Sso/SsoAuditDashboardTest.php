<?php

namespace Tests\Feature\Sso;

use App\Models\Sso\OpenKabSsoLog;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\BaseTestCase;

class SsoAuditDashboardTest extends BaseTestCase
{
    protected function grantAuditPermission(): void
    {
        $permission = Permission::findOrCreate('sso-audit-read', 'web');

        $team = Team::query()->where('name', 'administrator')->first();

        if (! $team) {
            return;
        }

        setPermissionsTeamId($team->id);

        $role = Role::query()->where('name', 'administrator')->where('guard_name', 'web')->first();

        if ($role) {
            $role->givePermissionTo($permission);
        }
    }

    #[Test]
    public function super_admin_dengan_permission_dapat_mengakses_dashboard()
    {
        $this->grantAuditPermission();

        $response = $this->get(route('sso.audit'));

        $response->assertStatus(200);
        $response->assertViewIs('sso.audit');
        $response->assertSee('Audit Akses SSO');
    }

    #[Test]
    public function ajax_menampilkan_data_log_sso()
    {
        $this->grantAuditPermission();
        $admin = User::first();
        $desaId = '9999999999';

        OpenKabSsoLog::create([
            'admin_id' => $admin->id,
            'desa_id' => $desaId,
            'attempt_time' => now(),
            'status' => 'failed',
            'reason_if_failed' => '2fa_belum_verifikasi',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Pest',
        ]);
        OpenKabSsoLog::create([
            'admin_id' => $admin->id,
            'desa_id' => $desaId,
            'attempt_time' => now(),
            'status' => 'success',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Pest',
        ]);

        $byDesa = $this->getJson(route('sso.audit').'?filter[desa_id]='.$desaId, [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $byDesa->assertOk();
        $byDesa->assertJsonCount(2, 'data');

        $filtered = $this->getJson(route('sso.audit').'?filter[desa_id]='.$desaId.'&filter[status]=failed', [
            'X-Requested-With' => 'XMLHttpRequest',
        ]);

        $filtered->assertOk();
        $filtered->assertJsonCount(1, 'data');
        $filtered->assertJsonPath('data.0.attributes.status', 'failed');
        $filtered->assertJsonPath('data.0.attributes.reason_if_failed', '2fa_belum_verifikasi');
    }

    #[Test]
    public function user_tanpa_permission_ditolak()
    {
        $user = User::factory()->create();

        $this->actingAsAdmin($user);

        $this->getJson(route('sso.audit'))->assertStatus(403);
    }

    #[Test]
    public function belum_login_diarahkan_ke_login()
    {
        Auth::logout();

        $this->get(route('sso.audit'))->assertRedirect(route('login'));
    }
}
