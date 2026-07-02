<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

uses()->group('pengaturan-web');

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the menu page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/menus')
        ->assertPathIs('/cms/menus')
        ->assertSee('Data Menu');

    ScreenshotHelper::saveIfEnabled($page, 'menu-page');
});

it('displays panel sumber menu url', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/menus')
        ->assertPathIs('/cms/menus')
        ->assertVisible('@panel-sumber-menu-url');

    ScreenshotHelper::saveIfEnabled($page, 'menu-panel-sumber');
});

it('displays panel struktur menu', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/menus')
        ->assertPathIs('/cms/menus')
        ->assertVisible('@panel-struktur-menu');

    ScreenshotHelper::saveIfEnabled($page, 'menu-panel-struktur');
});

it('displays all sumber menu url elements', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/menus')
        ->assertPathIs('/cms/menus')
        ->assertVisible('@select-sumber-menu-url')
        ->assertVisible('@input-nama-menu')
        ->assertVisible('@select-pilih-icon')
        ->assertVisible('@radio-link')
        ->assertVisible('@radio-halaman')
        ->assertVisible('@radio-kategori')
        ->assertVisible('@radio-modul')
        ->assertVisible('@input-url');

    ScreenshotHelper::saveIfEnabled($page, 'menu-sumber-elements');
});

it('displays simpan and tambah buttons on sumber menu panel', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/menus')
        ->assertPathIs('/cms/menus')
        ->assertVisible('@bt-simpan-sumber')
        ->assertVisible('@bt-tambah-sumber');

    ScreenshotHelper::saveIfEnabled($page, 'menu-sumber-buttons');
});

it('displays batal and simpan buttons on struktur menu panel', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/menus')
        ->assertPathIs('/cms/menus')
        ->assertVisible('@bt-batal-struktur')
        ->assertVisible('@bt-simpan-struktur');

    ScreenshotHelper::saveIfEnabled($page, 'menu-struktur-buttons');
});

