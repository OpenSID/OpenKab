<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

uses()->group('pengaturan-aplikasi');

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the identitas page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/identitas')
        ->assertPathIs('/pengaturan/identitas')
        ->assertSee('Identitas');

    ScreenshotHelper::saveIfEnabled($page, 'identitas-page');
});

it('displays ubah identitas button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/identitas')
        ->assertVisible('@bt-ubah-identitas');

    ScreenshotHelper::saveIfEnabled($page, 'identitas-ubah-button');
});

it('displays logo', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/identitas')
        ->assertPathIs('/pengaturan/identitas');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const logo = document.querySelector('[data-testid=\"logo-identitas\"]');
                if (logo && logo.naturalWidth > 0) {
                    resolve(true);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'identitas-logo');
});

it('displays info section with nama, kode, sebutan, nama provinsi, and kode provinsi', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/identitas')
        ->assertPathIs('/pengaturan/identitas')
        ->assertVisible('@info-identitas');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"info-identitas\"]');
                if (table) {
                    const rows = table.querySelectorAll('tbody tr');
                    if (rows.length >= 5) {
                        resolve(true);
                    } else {
                        setTimeout(check, 500);
                    }
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'identitas-info-section');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/identitas')
        ->assertPathIs('/pengaturan/identitas')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'identitas-no-errors');
});