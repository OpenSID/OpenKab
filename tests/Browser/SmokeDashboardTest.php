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
    SessionState::loginAndNavigate($this->user, '/dasbor')
        ->assertPathIs('/dasbor')
        ->assertSee('Dasbor')
        ->assertVisible('@filter-kabupaten')
        ->assertVisible('@filter-kecamatan')
        ->assertVisible('@filter-desa')
        ->assertVisible('@bt-filter')
        ->assertVisible('@summary-card-kecamatan')
        ->assertVisible('@summary-card-desa')
        ->assertVisible('@summary-card-penduduk')
        ->assertVisible('@summary-card-keluarga')
        ->assertVisible('@peta')
        ->assertVisible('@tabel-penduduk-block')
        ->assertVisible('@summary-penduduk');
});

it('applies filter and elements remain visible', function () {
    $page = SessionState::loginAndNavigate($this->user, '/dasbor')
        ->assertPathIs('/dasbor')
        ->assertVisible('@filter-kabupaten')
        ->assertVisible('@bt-filter');

    $page->script("$('#filter_kabupaten').val('50.01').trigger('change')");
    $page->click('@bt-filter');

    $page->assertVisible('@peta')
        ->assertVisible('@tabel-penduduk-block')
        ->assertVisible('@summary-penduduk');

    ScreenshotHelper::saveIfEnabled($page, 'dashboard-filter');
});
