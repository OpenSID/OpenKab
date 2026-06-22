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

it('opens the kategori artikel cms page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/categories')
        ->assertPathIs('/cms/categories')
        ->assertSee('Data Kategori');

    ScreenshotHelper::saveIfEnabled($page, 'kategori-cms-page');
});

it('displays tambah button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/categories')
        ->assertVisible('@bt-tambah');

    ScreenshotHelper::saveIfEnabled($page, 'kategori-cms-tambah-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/categories')
        ->assertVisible('@datatable-kategori-cms');

    ScreenshotHelper::saveIfEnabled($page, 'kategori-cms-datatable');
});

it('has at least 1 data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/categories')
        ->assertPathIs('/cms/categories')
        ->assertVisible('@datatable-kategori-cms');

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector(\'[data-testid="datatable-kategori-cms"]\');
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

    ScreenshotHelper::saveIfEnabled($page, 'kategori-cms-data-rows');
});

it('displays edit and delete buttons on data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/cms/categories')
        ->assertPathIs('/cms/categories');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-kategori-cms\"]');
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

    ScreenshotHelper::saveIfEnabled($page, 'kategori-cms-action-buttons');
});

