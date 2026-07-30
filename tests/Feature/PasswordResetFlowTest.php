<?php

use App\Models\PasswordHistory;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

uses(DatabaseTransactions::class);

beforeEach(function () {
    config(['password.check_hibp' => false]);
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => 'CurrentP@ssw0rd!',
        'force_password_reset' => true,
        'password_expires_at' => null,
    ]);
});

it('shows force password reset form when user requires reset', function () {
    $this->actingAs($this->user)
        ->get(route('password.reset.form'))
        ->assertStatus(200)
        ->assertViewIs('auth.force-password-reset');
});

it('redirects from force password reset form when user does not require reset', function () {
    $this->user->update(['force_password_reset' => false]);

    $this->actingAs($this->user)
        ->get(route('password.reset.form'))
        ->assertRedirect(route('dasbor'));
});

it('processes force password reset successfully', function () {
    $this->actingAs($this->user)
        ->post(route('password.reset.force'), [
            'password' => 'Str0ng!NewP@ssword',
            'password_confirmation' => 'Str0ng!NewP@ssword',
        ])
        ->assertRedirect(AppServiceProvider::HOME)
        ->assertSessionHas('success');

    $this->user->refresh();
    expect($this->user->force_password_reset)->toBeFalse();
    expect($this->user->password_expires_at)->not->toBeNull();
});

it('stores old password to history on force reset', function () {
    $this->actingAs($this->user)
        ->post(route('password.reset.force'), [
            'password' => 'Str0ng!NewP@ssword',
            'password_confirmation' => 'Str0ng!NewP@ssword',
        ]);

    $history = PasswordHistory::where('user_id', $this->user->id)->first();
    expect($history)->not->toBeNull();
    expect($history->reason)->toBe('forced_reset_completed');
    expect(Hash::check('CurrentP@ssw0rd!', $history->password))->toBeTrue();
});

it('rejects reused password in force reset', function () {
    $this->user->passwordHistory()->create([
        'password' => Hash::make('Str0ng!N3wP@ssword'),
        'reason' => 'password_change',
    ]);

    $this->actingAs($this->user)
        ->post(route('password.reset.force'), [
            'password' => 'Str0ng!N3wP@ssword',
            'password_confirmation' => 'Str0ng!N3wP@ssword',
        ])
        ->assertSessionHasErrors();
});

it('redirects from force reset when user does not require reset', function () {
    $this->user->update(['force_password_reset' => false]);

    $this->actingAs($this->user)
        ->post(route('password.reset.force'), [
            'password' => 'Str0ng!NewP@ssword',
            'password_confirmation' => 'Str0ng!NewP@ssword',
        ])
        ->assertRedirect(route('dasbor'));
});
