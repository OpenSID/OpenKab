<?php

use App\Mail\PasswordResetByAdminMail;
use App\Models\PasswordHistory;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

uses(DatabaseTransactions::class);

beforeEach(function () {
    config(['password.check_hibp' => false]);

    $this->team = Team::query()->where('name', 'administrator')->first() ?? Team::first();
    setPermissionsTeamId($this->team->id);

    $this->adminRole = Role::firstOrCreate([
        'name' => 'administrator',
        'team_id' => $this->team->id,
        'guard_name' => 'web',
    ]);
    $this->adminRole->givePermissionTo(Permission::findOrCreate('pengaturan-users-edit', 'web'));

    $this->admin = User::factory()->create(['email' => 'admin-'.uniqid().'@example.test']);
    $this->admin->team()->attach($this->team->id);
    $this->admin->assignRole($this->adminRole);

    $this->target = User::factory()->create([
        'email' => 'target-'.uniqid().'@example.test',
        'password' => 'CurrentP@ssw0rd!',
        'force_password_reset' => false,
        'password_expires_at' => null,
    ]);

    $this->target->team()->attach($this->team->id);

    Mail::fake();
});

it('lets an administrator reset a user password and forces a change at next login', function () {
    $response = $this->actingAs($this->admin)
        ->post(route('users.reset-password', $this->target->id));

    $response->assertRedirect();
    $response->assertSessionHas('password_sementara');

    $this->target->refresh();
    expect($this->target->force_password_reset)->toBeTrue();
    expect(Hash::check('CurrentP@ssw0rd!', $this->target->password))->toBeFalse();
});

it('shows the temporary password only once', function () {
    $this->actingAs($this->admin)
        ->post(route('users.reset-password', $this->target->id));

    $flashed = session('password_sementara');
    expect($flashed)->toBeString()->not->toBeEmpty();

    // Halaman pertama setelah reset menampilkan password sementara
    $this->actingAs($this->admin)
        ->get(route('users.index'))
        ->assertSee(e($flashed), false);

    // Halaman berikutnya tidak lagi menampilkan password sementara
    $this->actingAs($this->admin)
        ->get(route('users.index'))
        ->assertDontSee(e($flashed), false);
});

it('forces the target to change the temporary password at next login', function () {
    $this->actingAs($this->admin)
        ->post(route('users.reset-password', $this->target->id));

    $this->target->refresh();

    $this->actingAs($this->target)
        ->get(route('dasbor'))
        ->assertRedirect(route('password.reset.form'));
});

it('sends the temporary password by email to the target user', function () {
    $this->actingAs($this->admin)
        ->post(route('users.reset-password', $this->target->id));

    $password = session('password_sementara');

    Mail::assertSent(PasswordResetByAdminMail::class, function (PasswordResetByAdminMail $mail) use ($password) {
        return $mail->hasTo($this->target->email)
            && str_contains($mail->render(), e($password));
    });
});

it('keeps the reset and warns the admin when the email cannot be sent', function () {
    Mail::shouldReceive('to')->andThrow(new Exception('SMTP tidak tersedia'));

    $response = $this->actingAs($this->admin)
        ->post(route('users.reset-password', $this->target->id));

    $response->assertRedirect();
    $response->assertSessionHas('password_sementara');
    $response->assertSessionHas('warning');

    $this->target->refresh();
    expect($this->target->force_password_reset)->toBeTrue();
});

it('records the reset in the password history without storing the plain password', function () {
    $this->actingAs($this->admin)
        ->post(route('users.reset-password', $this->target->id));

    $password = session('password_sementara');

    $history = PasswordHistory::where('user_id', $this->target->id)
        ->where('reason', 'admin_reset')
        ->first();

    expect($history)->not->toBeNull();
    expect($history->password)->not->toBe($password);
});

it('records the reset in the activity log without the password', function () {
    // Logging dimatikan di console (AppServiceProvider); aktifkan untuk test ini
    activity()->enableLogging();

    $this->actingAs($this->admin)
        ->post(route('users.reset-password', $this->target->id));

    $password = session('password_sementara');

    $activity = Activity::query()
        ->where('description', 'reset_password')
        ->where('subject_id', $this->target->id)
        ->latest('id')
        ->first();

    expect($activity)->not->toBeNull();
    expect($activity->causer_id)->toBe($this->admin->id);
    expect(json_encode($activity->properties))->not->toContain($password);
});

it('rejects the reset when the actor is not an administrator', function () {
    $nonAdmin = User::factory()->create(['email' => 'biasa-'.uniqid().'@example.test']);
    $nonAdmin->team()->attach($this->team->id);

    $this->actingAs($nonAdmin)
        ->post(route('users.reset-password', $this->target->id))
        ->assertForbidden();

    $this->target->refresh();
    expect($this->target->force_password_reset)->toBeFalse();
});

it('rejects an administrator resetting their own password', function () {
    $this->actingAs($this->admin)
        ->post(route('users.reset-password', $this->admin->id))
        ->assertRedirect()
        ->assertSessionHas('error');

    $this->admin->refresh();
    expect($this->admin->force_password_reset)->toBeFalse();
    expect(session()->has('password_sementara'))->toBeFalse();
});
