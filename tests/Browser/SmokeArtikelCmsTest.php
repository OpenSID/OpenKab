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

it('opens the artikel cms page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/articles')
        ->assertPathIs('/cms/articles')
        ->assertSee('Data Artikel');

    ScreenshotHelper::saveIfEnabled($page, 'artikel-cms-page');
});

it('displays tambah button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/articles')
        ->assertVisible('@bt-tambah');

    ScreenshotHelper::saveIfEnabled($page, 'artikel-cms-tambah-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/articles')
        ->assertVisible('@datatable-artikel-cms');

    ScreenshotHelper::saveIfEnabled($page, 'artikel-cms-datatable');
});

it('has at least 1 data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/articles')
        ->assertPathIs('/cms/articles')
        ->assertVisible('@datatable-artikel-cms');

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector(\'[data-testid="datatable-artikel-cms"]\');
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

    ScreenshotHelper::saveIfEnabled($page, 'artikel-cms-data-rows');
});

it('displays edit and delete buttons on data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/articles')
        ->assertPathIs('/cms/articles');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-artikel-cms\"]');
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

    ScreenshotHelper::saveIfEnabled($page, 'artikel-cms-action-buttons');
});

