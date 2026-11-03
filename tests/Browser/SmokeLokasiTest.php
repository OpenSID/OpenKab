<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

uses()->group('pengaturan-peta');

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the lokasi page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/plan')
        ->assertPathIs('/plan')
        ->assertSee('Data Lokasi');

    ScreenshotHelper::saveIfEnabled($page, 'lokasi-page');
});

it('displays filter fields', function () {
    $page = SessionState::loginAndNavigate($this->user, '/plan')
        ->assertVisible('@bt-toggle-filter')
        ->assertVisible('@bt-bulk-delete')
        ->click('@bt-toggle-filter')
        ->assertVisible('@filter-status')
        ->assertVisible('@filter-point')
        ->assertVisible('@filter-kabupaten')
        ->assertVisible('@filter-kecamatan')
        ->assertVisible('@filter-desa')
        ->assertVisible('@bt-filter');

    ScreenshotHelper::saveIfEnabled($page, 'lokasi-filter-fields');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/plan')
        ->assertVisible('@datatable-lokasi');

    ScreenshotHelper::saveIfEnabled($page, 'lokasi-datatable');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/plan')
        ->assertPathIs('/plan')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'lokasi-no-errors');
});
