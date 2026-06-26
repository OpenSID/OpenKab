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

it('opens the kategori artikel page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/kategori/0')
        ->assertPathIs('/master/kategori/0')
        ->assertSee('Data Kategori Artikel');

    ScreenshotHelper::saveIfEnabled($page, 'kategori-artikel-page');
});

it('displays tambah button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/kategori/0')
        ->assertVisible('@bt-tambah');

    ScreenshotHelper::saveIfEnabled($page, 'kategori-artikel-tambah-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/kategori/0')
        ->assertVisible('@datatable-kategori');

    ScreenshotHelper::saveIfEnabled($page, 'kategori-artikel-datatable');
});

it('displays at least one data row in datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/kategori/0')
        ->assertPathIs('/master/kategori/0')
        ->assertVisible('@datatable-kategori');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-kategori\"]');
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

    ScreenshotHelper::saveIfEnabled($page, 'kategori-artikel-datatable-rows');
});

it('displays edit, delete, and rincian buttons on data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/kategori/0')
        ->assertPathIs('/master/kategori/0');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-kategori\"]');
                if (table) {
                    const rows = table.querySelectorAll('tbody tr');
                    if (rows.length > 0 && !rows[0].classList.contains('dataTables_empty')) {
                        const firstRow = rows[0];
                        const editBtn = firstRow.querySelector('[data-testid=\"bt-edit\"]');
                        const deleteBtn = firstRow.querySelector('[data-testid=\"bt-delete\"]');
                        const rincianBtn = firstRow.querySelector('[data-testid=\"bt-rincian\"]');
                        resolve(!!(editBtn && deleteBtn && rincianBtn));
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

    ScreenshotHelper::saveIfEnabled($page, 'kategori-artikel-action-buttons');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/master/kategori/0')
        ->assertPathIs('/master/kategori/0')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'kategori-artikel-no-errors');
});
