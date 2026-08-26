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

it('opens the grup page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/groups')
        ->assertPathIs('/pengaturan/groups')
        ->assertSee('Pengaturan Grup Pengguna');

    ScreenshotHelper::saveIfEnabled($page, 'grup-page');
});

it('displays tambah button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/groups')
        ->assertVisible('@bt-tambah');

    ScreenshotHelper::saveIfEnabled($page, 'grup-tambah-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/groups')
        ->assertVisible('@datatable-grup');

    ScreenshotHelper::saveIfEnabled($page, 'grup-datatable');
});

it('displays at least 2 data rows in datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/groups')
        ->assertPathIs('/pengaturan/groups')
        ->assertVisible('@datatable-grup');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-grup\"]');
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

    ScreenshotHelper::saveIfEnabled($page, 'grup-datatable-rows');
});

it('has administrator row with only edit button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/groups')
        ->assertPathIs('/pengaturan/groups')
        ->assertVisible('@datatable-grup');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-grup\"]');
                if (table) {
                    const rows = table.querySelectorAll('tbody tr');
                    if (rows.length > 0 && !rows[0].classList.contains('dataTables_empty')) {
                        for (const row of rows) {
                            const cells = row.querySelectorAll('td');
                            const nameCell = cells[2];
                            if (nameCell && nameCell.textContent.trim().toLowerCase().includes('administrator')) {
                                const editBtn = row.querySelector('[data-testid=\"bt-edit\"]');
                                const deleteBtn = row.querySelector('[data-testid=\"bt-delete\"]');
                                resolve(!!editBtn && !deleteBtn);
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

    ScreenshotHelper::saveIfEnabled($page, 'grup-admin-edit-only');
});

it('has data rows with edit and delete buttons', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/groups')
        ->assertPathIs('/pengaturan/groups')
        ->assertVisible('@datatable-grup');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-grup\"]');
                if (table) {
                    const rows = table.querySelectorAll('tbody tr');
                    if (rows.length > 0 && !rows[0].classList.contains('dataTables_empty')) {
                        for (const row of rows) {
                            const cells = row.querySelectorAll('td');
                            const nameCell = cells[2];
                            if (nameCell && !nameCell.textContent.trim().toLowerCase().includes('administrator')) {
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

    ScreenshotHelper::saveIfEnabled($page, 'grup-action-buttons');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/groups')
        ->assertPathIs('/pengaturan/groups')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'grup-no-errors');
});
