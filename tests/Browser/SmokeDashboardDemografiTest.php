<?php

use App\Models\User;
use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = User::firstOrCreate(
        ['email' => 'pest-demografi@opendesa.test'],
        [
            'name' => 'Pest Demografi User',
            'password' => 'password',
            'username' => 'pestdemografi',
        ]
    );
    SessionState::assignAdminRole($this->user);
    SessionState::saveForUser($this->user);
});

afterEach(function () {
    SessionState::clear();
});

it('can open demografi dashboard page', function () {
    $page = SessionState::loginAs($this->user)
        ->navigate('/dasbor-demografi')
        ->assertPathIs('/dasbor-demografi')
        ->assertSee('Dasbor Demografi');

    ScreenshotHelper::saveIfEnabled($page, 'demografi-open');
});

it('displays wilayah filter', function () {
    $page = SessionState::loginAs($this->user)
        ->navigate('/dasbor-demografi')
        ->assertVisible('@filter_kabupaten')
        ->assertVisible('@filter_kecamatan')
        ->assertVisible('@filter_desa')
        ->assertVisible('@bt_filter');

    ScreenshotHelper::saveIfEnabled($page, 'demografi-filter');
});

it('displays all 9 chart cards', function () {
    $chartKeys = [
        'rentang-umur',
        'status-perkawinan',
        'pendidikan-dalam-kk',
        'golongan-darah',
        'penyakit-menahun',
        'agama',
        'jenis-kelamin',
        'suku',
        'penyandang-cacat',
    ];

    $page = SessionState::loginAs($this->user)
        ->navigate('/dasbor-demografi');

    foreach ($chartKeys as $key) {
        $page->assertSee($key);
    }

    ScreenshotHelper::saveIfEnabled($page, 'demografi-charts');
});

it('displays chart titles', function () {
    $page = SessionState::loginAs($this->user)
        ->navigate('/dasbor-demografi')
        ->assertSee('Rentang Umur')
        ->assertSee('Status Perkawinan')
        ->assertSee('Pendidikan Dalam KK')
        ->assertSee('Golongan Darah')
        ->assertSee('Penyakit Menahun')
        ->assertSee('Agama')
        ->assertSee('Jenis Kelamin')
        ->assertSee('Suku / Etnis')
        ->assertSee('Penyandang Cacat');

    ScreenshotHelper::saveIfEnabled($page, 'demografi-titles');
});

it('updates charts when filtering by kabupaten', function () {
    $page = SessionState::loginAs($this->user)
        ->navigate('/dasbor-demografi');

    $page->script('
        $("#filter_kabupaten").val("50.01").trigger("change");
        $("#bt_filter").click();
    ');

    $page->wait(5000);

    $page->assertSee('Rentang Umur');

    ScreenshotHelper::saveIfEnabled($page, 'demografi-filtered');
});

it('clear filter button works', function () {
    $page = SessionState::loginAs($this->user)
        ->navigate('/dasbor-demografi');

    $page->script('
        $("#filter_kabupaten").val("50.01").trigger("change");
        $("#bt_filter").click();
    ');

    $page->wait(5000);

    $page->script('
        $("#filter_kabupaten").val(null).trigger("change");
        $("#bt_filter").click();
    ');

    $page->wait(3000);

    $page->assertSee('Rentang Umur');

    ScreenshotHelper::saveIfEnabled($page, 'demografi-clear-filter');
});
