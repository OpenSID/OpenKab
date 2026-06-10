<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('displays all dashboard elements', function () {
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

    $page = SessionState::loginAndNavigate($this->user, '/dasbor-demografi')
        ->wait(3000)
        ->assertPathIs('/dasbor-demografi')
        ->assertSee('Dasbor Demografi')
        ->assertVisible('@filter_kabupaten')
        ->assertVisible('@filter_kecamatan')
        ->assertVisible('@filter_desa')
        ->assertVisible('@bt_filter');

    foreach ($chartKeys as $key) {
        $page->assertSee($key);
    }

    $page->assertSee('Rentang Umur')
        ->assertSee('Status Perkawinan')
        ->assertSee('Pendidikan Dalam KK')
        ->assertSee('Golongan Darah')
        ->assertSee('Penyakit Menahun')
        ->assertSee('Agama')
        ->assertSee('Jenis Kelamin')
        ->assertSee('Suku / Etnis')
        ->assertSee('Penyandang Cacat');

    ScreenshotHelper::saveIfEnabled($page, 'demografi-all');
});

it('updates charts when filtering by kabupaten', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dasbor-demografi');

    SessionState::applyFilter($page, '50.01');
    $page->assertSee('Rentang Umur');

    ScreenshotHelper::saveIfEnabled($page, 'demografi-filtered');
});

it('clear filter button works', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dasbor-demografi');

    SessionState::applyFilter($page, '50.01');
    SessionState::clearFilter($page);
    $page->assertSee('Rentang Umur');

    ScreenshotHelper::saveIfEnabled($page, 'demografi-clear-filter');
});
