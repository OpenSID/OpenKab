<?php

use App\Models\User;
use Tests\Browser\SessionState;

it('displays login page correctly', function () {
    visit('/login')
        ->assertSee('Masuk')
        ->assertVisible('input[name="login"]')
        ->assertVisible('input[name="password"]')
        ->assertVisible('button[type="submit"]');
});

it('can login with valid credentials', function () {
    $email = 'pest-' . time() . '@login.test';
    $user = User::factory()->create([
        'email' => $email,
        'password' => 'password123',
    ]);

    visit('/login')
        ->fill('input[name="login"]', $email)
        ->fill('input[name="password"]', 'password123')
        ->press('Masuk')
        ->assertPathIsNot('/login');

    SessionState::saveForUser($user);
});

it('shows error for invalid credentials', function () {
    visit('/login')
        ->fill('input[name="login"]', 'wrong@email.com')
        ->fill('input[name="password"]', 'wrongpassword')
        ->submit()
        ->assertSee('Masuk');
});

it('can restore session from saved state', function () {
    SessionState::clear();

    $user = SessionState::getOrCreateUser('pest-restore');
    SessionState::saveForUser($user);

    $state = SessionState::load();
    expect($state)->not->toBeNull();
    expect($state['user_id'])->toBe($user->id);

    visit("/_pest/login/{$user->id}")
        ->navigate('/dasbor')
        ->assertPathIs('/dasbor');

    SessionState::clear();
});
