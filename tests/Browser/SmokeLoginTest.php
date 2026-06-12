<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function(){
    \App\Models\Setting::updateOrCreate(
        ['key' => 'captcha_enabled'],
        ['value' => 'false']
    );
});

it('displays login page correctly', function () {
    $page = visit('/login')
        ->assertSee('Masuk')
        ->assertVisible('@login-email')
        ->assertVisible('@login-password')
        ->assertVisible('@login-submit');

    ScreenshotHelper::saveIfEnabled($page, 'login-page');
});

it('can login with valid credentials', function () {
    $email = 'pest-login@test.com';
    $password = 'Oytrettt@123Quat';
    $user = User::where('email', $email)->first()
        ?? User::factory()->create([
            'email' => $email,
            'password' => Hash::make($password),
        ]);
    $user->password = $password;
    $user->save();
    $page = visit('/login');

    $page->script("
        document.querySelector('[data-testid=login-email]').value = '".addslashes($email)."';
        document.querySelector('[data-testid=login-password]').value = '".$password."';
        document.querySelector('[data-testid=login-submit]').click();
    ");
    
    $page->assertPathIsNot('/login');
    
    ScreenshotHelper::saveIfEnabled($page, 'login-success');
});

it('shows error for invalid credentials', function () {
    $page = visit('/login');

    $page->script("
        document.querySelector('[data-testid=login-email]').value = 'wrong@email.com';
        document.querySelector('[data-testid=login-password]').value = 'wrongpassword';
        document.querySelector('[data-testid=login-submit]').click();
    ");
    
    $page->assertSee('Masuk');

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
