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
        ->assertVisible('@filter-status')
        ->assertVisible('@filter-point');

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
