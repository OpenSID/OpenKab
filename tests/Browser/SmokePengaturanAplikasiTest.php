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

it('opens the pengaturan aplikasi page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/settings')
        ->assertPathIs('/pengaturan/settings')
        ->assertSee('Data Setting');

    ScreenshotHelper::saveIfEnabled($page, 'pengaturan-aplikasi-page');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/settings')
        ->assertVisible('@datatable-settings');

    ScreenshotHelper::saveIfEnabled($page, 'pengaturan-aplikasi-datatable');
});

it('displays edit button on data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/settings')
        ->assertPathIs('/pengaturan/settings')
        ->assertVisible('@datatable-settings');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-settings\"]');
                if (table) {
                    const rows = table.querySelectorAll('tbody tr');
                    if (rows.length > 0 && !rows[0].classList.contains('dataTables_empty')) {
                        const firstRow = rows[0];
                        const editBtn = firstRow.querySelector('.edit');
                        resolve(!!editBtn);
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

    ScreenshotHelper::saveIfEnabled($page, 'pengaturan-aplikasi-edit-button');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/settings')
        ->assertPathIs('/pengaturan/settings')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'pengaturan-aplikasi-no-errors');
});