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

it('opens the daftar unduhan page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/downloads')
        ->assertPathIs('/cms/downloads')
        ->assertSee('Data Unduhan');

    ScreenshotHelper::saveIfEnabled($page, 'unduhan-page');
});

it('displays tambah button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/downloads')
        ->assertVisible('@bt-tambah');

    ScreenshotHelper::saveIfEnabled($page, 'unduhan-tambah-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/downloads')
        ->assertVisible('@datatable-unduhan');

    ScreenshotHelper::saveIfEnabled($page, 'unduhan-datatable');
});

it('has at least 1 data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/downloads')
        ->assertPathIs('/cms/downloads')
        ->assertVisible('@datatable-unduhan');

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector(\'[data-testid="datatable-unduhan"]\');
                if (table) {
                    const rows = table.querySelectorAll(\'tbody tr\');
                    if (rows.length > 0 && !rows[0].classList.contains(\'dataTables_empty\')) {
                        resolve(true);
                    } else {
                        setTimeout(check, 500);
                    }
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })',
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'unduhan-data-rows');
});

it('displays edit and delete buttons on data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/downloads')
        ->assertPathIs('/cms/downloads');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-unduhan\"]');
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

    ScreenshotHelper::saveIfEnabled($page, 'unduhan-action-buttons');
});

