<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

uses()->group('pengaturan-opensid');

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the artikel page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/artikel')
        ->assertPathIs('/master/artikel')
        ->assertSee('Data Artikel');

    ScreenshotHelper::saveIfEnabled($page, 'artikel-page');
});

it('displays tambah button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/artikel')
        ->assertVisible('@bt-tambah');

    ScreenshotHelper::saveIfEnabled($page, 'artikel-tambah-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/artikel')
        ->assertVisible('@datatable-artikel');

    ScreenshotHelper::saveIfEnabled($page, 'artikel-datatable');
});

it('displays at least one data row in datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/artikel')
        ->assertPathIs('/master/artikel')
        ->assertVisible('@datatable-artikel');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-artikel\"]');
                if (table) {
                    const rows = table.querySelectorAll('tbody tr');
                    if (rows.length > 0 && !rows[0].classList.contains('dataTables_empty')) {
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

    ScreenshotHelper::saveIfEnabled($page, 'artikel-datatable-rows');
});

it('displays edit and delete buttons on data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/artikel')
        ->assertPathIs('/master/artikel');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-artikel\"]');
                if (table) {
                    const rows = table.querySelectorAll('tbody tr');
                    if (rows.length > 0 && !rows[0].classList.contains('dataTables_empty')) {
                        const firstRow = rows[0];
                        const editBtn = firstRow.querySelector('[data-testid=\"bt-edit\"]');
                        const deleteBtn = firstRow.querySelector('[data-testid=\"bt-delete\"]');
                        resolve(!!(editBtn && deleteBtn));
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

    ScreenshotHelper::saveIfEnabled($page, 'artikel-action-buttons');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/artikel')
        ->assertPathIs('/master/artikel')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'artikel-no-errors');
});
