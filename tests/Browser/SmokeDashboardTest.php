<?php

use App\Models\User;
use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

it('can access dashboard after quick login', function () {
    $user = User::firstOrCreate(
        ['email' => 'pest-test@opendesa.test'],
        [
            'name' => 'Pest Test User',
            'password' => 'password',
            'username' => 'pesttest',
        ]
    );
    SessionState::assignAdminRole($user);

    $page = visit("/_pest/login/{$user->id}")
        ->navigate('/dasbor')
        ->assertPathIs('/dasbor');

    ScreenshotHelper::saveIfEnabled($page, 'dashboard');
});
