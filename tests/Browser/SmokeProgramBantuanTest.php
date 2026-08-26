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

it('opens the program bantuan page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/bantuan')
        ->assertPathIs('/master/bantuan')
        ->assertSee('Data Program Bantuan');

    ScreenshotHelper::saveIfEnabled($page, 'program-bantuan-page');
});

it('displays tambah button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/bantuan')
        ->assertVisible('@bt-tambah');

    ScreenshotHelper::saveIfEnabled($page, 'program-bantuan-tambah-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/bantuan')
        ->assertVisible('@datatable-bantuan');

    ScreenshotHelper::saveIfEnabled($page, 'program-bantuan-datatable');
});

it('displays at least one data row in datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/bantuan')
        ->assertPathIs('/master/bantuan')
        ->assertVisible('@datatable-bantuan');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-bantuan\"]');
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

    ScreenshotHelper::saveIfEnabled($page, 'program-bantuan-datatable-rows');
});

it('displays edit and delete buttons on data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/bantuan')
        ->assertPathIs('/master/bantuan');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-bantuan\"]');
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

    ScreenshotHelper::saveIfEnabled($page, 'program-bantuan-action-buttons');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/bantuan')
        ->assertPathIs('/master/bantuan')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'program-bantuan-no-errors');
});
