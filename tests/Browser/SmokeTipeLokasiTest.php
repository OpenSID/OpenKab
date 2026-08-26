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

it('opens the tipe lokasi page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/point')
        ->assertPathIs('/point')
        ->assertSee('Data Tipe Lokasi');

    ScreenshotHelper::saveIfEnabled($page, 'tipe-lokasi-page');
});

it('displays filter button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/point')
        ->assertVisible('@bt-filter');

    ScreenshotHelper::saveIfEnabled($page, 'tipe-lokasi-filter-button');
});

it('displays tambah button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/point')
        ->assertVisible('@bt-tambah');

    ScreenshotHelper::saveIfEnabled($page, 'tipe-lokasi-tambah-button');
});

it('displays hapus multi button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/point')
        ->assertVisible('@bt-hapus-multi');

    ScreenshotHelper::saveIfEnabled($page, 'tipe-lokasi-hapus-multi-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/point')
        ->assertVisible('@datatable-point');

    ScreenshotHelper::saveIfEnabled($page, 'tipe-lokasi-datatable');
});

it('displays at least one data row in datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/point')
        ->assertPathIs('/point')
        ->assertVisible('@datatable-point');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-point\"]');
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

    ScreenshotHelper::saveIfEnabled($page, 'tipe-lokasi-datatable-rows');
});

it('displays checkbox on data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/point')
        ->assertPathIs('/point');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-point\"]');
                if (table) {
                    const rows = table.querySelectorAll('tbody tr');
                    if (rows.length > 0 && !rows[0].classList.contains('dataTables_empty')) {
                        const firstRow = rows[0];
                        const checkbox = firstRow.querySelector('input.select-checkbox');
                        resolve(!!checkbox);
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

    ScreenshotHelper::saveIfEnabled($page, 'tipe-lokasi-checkbox');
});

it('displays edit, delete, rincian, and lock buttons on data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/point')
        ->assertPathIs('/point');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-point\"]');
                if (table) {
                    const rows = table.querySelectorAll('tbody tr');
                    if (rows.length > 0 && !rows[0].classList.contains('dataTables_empty')) {
                        const firstRow = rows[0];
                        const editBtn = firstRow.querySelector('[data-testid=\"bt-edit\"]');
                        const deleteBtn = firstRow.querySelector('[data-testid=\"bt-delete\"]');
                        const rincianBtn = firstRow.querySelector('[data-testid=\"bt-rincian\"]');
                        const lockBtn = firstRow.querySelector('[data-testid=\"bt-lock\"]');
                        resolve(!!(editBtn && deleteBtn && rincianBtn && lockBtn));
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

    ScreenshotHelper::saveIfEnabled($page, 'tipe-lokasi-action-buttons');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/point')
        ->assertPathIs('/point')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'tipe-lokasi-no-errors');
});
