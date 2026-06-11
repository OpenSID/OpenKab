<?php

use App\Models\User;
use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

it('displays login page correctly', function () {
    $page = visit('/login')
        ->assertSee('Masuk')
        ->assertVisible('@login-email')
        ->assertVisible('@login-password')
        ->assertVisible('@login-submit');

    ScreenshotHelper::saveIfEnabled($page, 'login-page');
});

it('can login with valid credentials', function () {
    $email = 'pest-' . time() . '@login.test';
    $user = User::factory()->create([
        'email' => $email,
        'password' => 'paSsword@123Quat',
    ]);
    SessionState::assignAdminRole($user);

    $page = visit('/login')
        ->fill('@login-email', $email)
        ->fill('@login-password', 'paSsword@123Quat')
        ->press('Masuk')
        ->assertPathIsNot('/login');

    SessionState::saveForUser($user);
    ScreenshotHelper::saveIfEnabled($page, 'login-success');
});

it('shows error for invalid credentials', function () {
    $page = visit('/login')
        ->fill('@login-email', 'wrong@email.com')
        ->fill('@login-password', 'wrongpassword')
        ->press('@login-submit')
        ->assertSee('Masuk');

    ScreenshotHelper::saveIfEnabled($page, 'login-error');
});

it('can restore session from saved state', function () {
    SessionState::clear();

    $user = SessionState::getOrCreateUser('pest-restore');
    SessionState::assignAdminRole($user);
    SessionState::saveForUser($user);

    $state = SessionState::load();
    expect($state)->not->toBeNull();
    expect($state['user_id'])->toBe($user->id);

    $page = visit("/_pest/login/{$user->id}")
        ->navigate('/dasbor')
        ->assertPathIs('/dasbor');

    SessionState::clear();
    ScreenshotHelper::saveIfEnabled($page, 'session-restore');
});
