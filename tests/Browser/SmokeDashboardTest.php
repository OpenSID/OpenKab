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
    $page = SessionState::loginAndNavigate($this->user, '/dasbor')
        ->wait(3000)
        ->assertPathIs('/dasbor')
        ->assertSee('Dasbor')
        ->assertVisible('@filter_kabupaten')
        ->assertVisible('@filter_kecamatan')
        ->assertVisible('@filter_desa')
        ->assertVisible('@bt_filter')
        ->assertVisible('#summary_block')
        ->assertSee('kecamatan')
        ->assertSee('jumlah penduduk')
        ->assertSee('jumlah keluarga')
        ->assertVisible('#map')
        ->assertVisible('#tabel_penduduk_block')
        ->assertVisible('#summary-penduduk');

    ScreenshotHelper::saveIfEnabled($page, 'dashboard-all');
});

it('updates summary when filtering by kabupaten', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dasbor');

    SessionState::applyFilter($page, '50.01');
    $page->assertVisible('#summary_block');

    ScreenshotHelper::saveIfEnabled($page, 'dashboard-filtered');
});

it('clear filter button works', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dasbor');

    SessionState::applyFilter($page, '50.01');
    SessionState::clearFilter($page);
    $page->assertVisible('#summary_block');

    ScreenshotHelper::saveIfEnabled($page, 'dashboard-clear-filter');
});
