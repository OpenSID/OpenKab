<?php

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->team = Team::query()->where('name', 'administrator')->first() ?? Team::first();
    setPermissionsTeamId($this->team->id);

    // Group uji: punya role sendiri tanpa izin dasbor
    $this->groupRole = Role::firstOrCreate([
        'name' => 'group-uji-'.uniqid(),
        'team_id' => $this->team->id,
        'guard_name' => 'web',
    ]);

    $this->user = User::factory()->create(['email' => 'anggota-'.uniqid().'@example.test']);
    $this->user->team()->attach($this->team->id);
    $this->user->assignRole($this->groupRole);
});

/**
 * Hitung link yang menuju /dasbor. Link logo (dashboard_url) selalu ada,
 * jadi item menu Dasbor tampil jika jumlahnya bertambah satu.
 */
function hitungLinkDasbor(string $html): int
{
    return preg_match_all('#href="https?://[^/"]+/dasbor"#', $html);
}

function grantDasborPermission(Role $role, string $name): void
{
    $role->givePermissionTo(Permission::findOrCreate($name, 'web'));
}

it('denies the dasbor page to a group without dasbor-read', function () {
    $this->actingAs($this->user)
        ->get(route('dasbor'))
        ->assertRedirect(route('beranda'));
});

it('denies the dasbor demografi page to a group without dasbor-demografi-read', function () {
    $this->actingAs($this->user)
        ->get(route('dasbor-demografi'))
        ->assertRedirect(route('beranda'));
});

it('allows the dasbor page once dasbor-read is granted to the group', function () {
    grantDasborPermission($this->groupRole, 'dasbor-read');

    $this->actingAs($this->user)
        ->get(route('dasbor'))
        ->assertOk();
});

it('allows the dasbor demografi page once dasbor-demografi-read is granted', function () {
    grantDasborPermission($this->groupRole, 'dasbor-demografi-read');

    $this->actingAs($this->user)
        ->get(route('dasbor-demografi'))
        ->assertOk();
});

it('applies a permission change without recreating the account', function () {
    $this->actingAs($this->user)->get(route('dasbor'))->assertRedirect(route('beranda'));

    grantDasborPermission($this->groupRole, 'dasbor-read');
    // Hapus cache izin spatie agar perubahan terbaca pada permintaan berikutnya
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $this->actingAs($this->user)->get(route('dasbor'))->assertOk();
});

function jalankanMigrasiMenuDasbor(string $arah = 'up'): void
{
    $migration = require database_path('migrations/2026_11_23_000002_tambah_menu_dasbor_ke_group.php');
    $migration->{$arah}();
    app(PermissionRegistrar::class)->forgetCachedPermissions();
}

it('hides the dasbor menu from a group without dasbor-read', function () {
    // Item menu Dasbor harus ada di menu group agar gate 'can' benar-benar diuji
    jalankanMigrasiMenuDasbor();

    // Halaman profil sendiri bisa diakses tanpa izin khusus dan memuat sidebar
    $this->actingAs($this->user)
        ->get(route('profile.edit', $this->user->id))
        ->assertOk()
        ->assertDontSee('Dasbor Demografi')
        ->tap(fn ($response) => expect(hitungLinkDasbor($response->getContent()))->toBe(1)); // hanya link logo
});

it('shows the dasbor menu to a group with dasbor-read', function () {
    jalankanMigrasiMenuDasbor();
    grantDasborPermission($this->groupRole, 'dasbor-read');

    $this->actingAs($this->user)
        ->get(route('profile.edit', $this->user->id))
        ->assertOk()
        ->assertDontSee('Dasbor Demografi')
        ->tap(fn ($response) => expect(hitungLinkDasbor($response->getContent()))->toBe(2)); // logo + item menu
});

it('grants dasbor permissions only to administrator, superadmin_daerah and kabupaten roles', function () {
    $roleTujuan = collect(['administrator', 'superadmin_daerah', 'kabupaten'])
        ->map(fn ($nama) => Role::firstOrCreate(['name' => $nama, 'team_id' => $this->team->id, 'guard_name' => 'web']));

    // Role lain di team yang sama tidak boleh menerima izin
    $lainnya = Role::firstOrCreate([
        'name' => 'group-lain-'.uniqid(),
        'team_id' => $this->team->id,
        'guard_name' => 'web',
    ]);

    $migration = require database_path('migrations/2026_11_23_000001_grant_dasbor_permissions_to_existing_groups.php');
    $migration->up();

    $roleTujuan->each(function (Role $role) {
        $role->refresh();
        expect($role->hasPermissionTo('dasbor-read'))->toBeTrue();
        expect($role->hasPermissionTo('dasbor-demografi-read'))->toBeTrue();
    });

    $lainnya->refresh();
    expect($lainnya->hasPermissionTo('dasbor-read'))->toBeFalse();
    expect($lainnya->hasPermissionTo('dasbor-demografi-read'))->toBeFalse();
});

it('inserts dasbor menu items at the top of existing group menus without duplicates', function () {
    $team = Team::create(['name' => 'group-menu-'.uniqid(), 'menu' => []]);
    $team->update([
        'menu' => [['text' => 'Profile Kependudukan', 'permission' => 'datapokok', 'url' => 'penduduk']],
        'menu_order' => [['text' => 'Profile Kependudukan', 'permission' => 'datapokok', 'url' => 'penduduk']],
    ]);

    jalankanMigrasiMenuDasbor();
    jalankanMigrasiMenuDasbor(); // jalankan dua kali: tidak boleh menduplikasi

    $team->refresh();
    $permissions = collect($team->menu)->pluck('permission')->all();

    expect($permissions)->toBe(['dasbor', 'dasbor-demografi', 'datapokok']);
    expect(collect($team->menu_order)->pluck('permission')->all())->toBe(['dasbor', 'dasbor-demografi', 'datapokok']);
    expect($team->menu[0]['can'])->toBe('dasbor-read');
});

it('removes only the dasbor menu items when rolled back', function () {
    $team = Team::create(['name' => 'group-menu-'.uniqid(), 'menu' => []]);
    $team->update([
        'menu' => [['text' => 'Profile Kependudukan', 'permission' => 'datapokok', 'url' => 'penduduk']],
    ]);

    jalankanMigrasiMenuDasbor();
    jalankanMigrasiMenuDasbor('down');

    $team->refresh();
    expect(collect($team->menu)->pluck('permission')->all())->toBe(['datapokok']);
});

it('shows the empty beranda page to a group without any dasbor permission', function () {
    $this->actingAs($this->user)
        ->get(route('beranda'))
        ->assertOk()
        ->assertViewIs('beranda.index');
});
