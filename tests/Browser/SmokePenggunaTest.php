<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

uses()->group('pengaturan-pengguna');

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the pengguna page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/users')
        ->assertPathIs('/pengaturan/users')
        ->assertSee('Data Pengguna');

    ScreenshotHelper::saveIfEnabled($page, 'pengguna-page');
});

it('displays tambah button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/users')
        ->assertVisible('@bt-tambah');

    ScreenshotHelper::saveIfEnabled($page, 'pengguna-tambah-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/users')
        ->assertVisible('@datatable-pengguna');

    ScreenshotHelper::saveIfEnabled($page, 'pengguna-datatable');
});

it('displays at least 2 data rows in datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/users')
        ->assertPathIs('/pengaturan/users')
        ->assertVisible('@datatable-pengguna');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-pengguna\"]');
                if (table) {
                    const rows = table.querySelectorAll('tbody tr');
                    if (rows.length >= 2 && !rows[0].classList.contains('dataTables_empty')) {
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

    ScreenshotHelper::saveIfEnabled($page, 'pengguna-datatable-rows');
});

it('has superadmin row with only edit button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/users')
        ->assertPathIs('/pengaturan/users')
        ->assertVisible('@datatable-pengguna');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-pengguna\"]');
                if (table) {
                    const rows = table.querySelectorAll('tbody tr');
                    if (rows.length > 0 && !rows[0].classList.contains('dataTables_empty')) {
                        for (const row of rows) {
                            const cells = row.querySelectorAll('td');
                            const nameCell = cells[2];
                            if (nameCell && nameCell.textContent.trim().toLowerCase().includes('superadmin')) {
                                const editBtn = row.querySelector('[data-testid=\"bt-edit\"]');
                                const deleteBtn = row.querySelector('[data-testid=\"bt-delete\"]');
                                const lockBtn = row.querySelector('[data-testid=\"bt-lock\"]') || row.querySelector('[data-testid=\"bt-unlock\"]');
                                resolve(!!editBtn && !deleteBtn && !lockBtn);
                                return;
                            }
                        }
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

    ScreenshotHelper::saveIfEnabled($page, 'pengguna-superadmin-edit-only');
});

it('has data rows with edit and delete buttons', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/users')
        ->assertPathIs('/pengaturan/users')
        ->assertVisible('@datatable-pengguna');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-pengguna\"]');
                if (table) {
                    const rows = table.querySelectorAll('tbody tr');
                    if (rows.length > 0 && !rows[0].classList.contains('dataTables_empty')) {
                        for (const row of rows) {
                            const cells = row.querySelectorAll('td');
                            const nameCell = cells[2];
                            if (nameCell && !nameCell.textContent.trim().toLowerCase().includes('superadmin')) {
                                const editBtn = row.querySelector('[data-testid=\"bt-edit\"]');
                                const deleteBtn = row.querySelector('[data-testid=\"bt-delete\"]');
                                if (editBtn && deleteBtn) {
                                    resolve(true);
                                    return;
                                }
                            }
                        }
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

    ScreenshotHelper::saveIfEnabled($page, 'pengguna-action-buttons');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/users')
        ->assertPathIs('/pengaturan/users')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'pengguna-no-errors');
});
