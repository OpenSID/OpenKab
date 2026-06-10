<?php

use App\Models\User;

it('can access dashboard after quick login', function () {
    $user = User::firstOrCreate(
        ['email' => 'pest-test@opendesa.test'],
        [
            'name' => 'Pest Test User',
            'password' => 'password',
            'username' => 'pesttest',
        ]
    );

    visit("/_pest/login/{$user->id}")
        ->navigate('/dasbor')
        ->assertPathIs('/dasbor');
});
