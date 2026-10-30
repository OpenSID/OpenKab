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

it('opens the lokasi page with import controls', function () {
    $page = SessionState::loginAndNavigate($this->user, '/plan')
        ->assertPathIs('/plan')
        ->assertSee('Data Lokasi')
        ->assertVisible('@bt-import')
        ->assertVisible('@bt-tambah');

    ScreenshotHelper::saveIfEnabled($page, 'lokasi-import-controls');
});

it('opens the import modal with jenis, kategori and template controls', function () {
    $page = SessionState::loginAndNavigate($this->user, '/plan')
        ->assertVisible('@bt-import')
        ->click('@bt-import')
        ->assertVisible('@import-jenis')
        ->assertVisible('@import-kategori')
        ->assertVisible('@bt-download-template')
        ->assertVisible('@import-file')
        ->assertVisible('@bt-submit-import');

    ScreenshotHelper::saveIfEnabled($page, 'lokasi-import-modal');
});

it('has no javascript errors on the lokasi page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/plan')
        ->assertPathIs('/plan')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'lokasi-import-no-errors');
});
