<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

$chartKeys = [
    'rentang-umur', 'status-perkawinan', 'pendidikan-dalam-kk',
    'golongan-darah', 'penyakit-menahun', 'agama',
    'jenis-kelamin', 'suku', 'penyandang-cacat',
];

it('displays all dashboard elements', function () use ($chartKeys) {
    $page = SessionState::loginAndNavigate($this->user, '/dasbor-demografi')
        ->assertPathIs('/dasbor-demografi')
        ->assertSee('Dasbor Demografi')
        ->assertVisible('@filter-kabupaten')
        ->assertVisible('@filter-kecamatan')
        ->assertVisible('@filter-desa')
        ->assertVisible('@bt-filter');

    foreach ($chartKeys as $key) {
        $page->assertVisible("@chart-item-{$key}");
    }
});

it('applies filter and all charts remain visible', function () use ($chartKeys) {
    $page = SessionState::loginAndNavigate($this->user, '/dasbor-demografi')
        ->assertPathIs('/dasbor-demografi')
        ->assertVisible('@filter-kabupaten')
        ->assertVisible('@bt-filter');

    $page->script("$('#filter_kabupaten').val('50.01').trigger('change')");
    $page->click('@bt-filter');

    foreach ($chartKeys as $key) {
        $page->assertVisible("@chart-item-{$key}");
    }

    ScreenshotHelper::saveIfEnabled($page, 'demografi-filter');
});
