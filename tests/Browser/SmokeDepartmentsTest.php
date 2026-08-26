<?php

use App\Models\Department;
use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    Department::firstOrCreate(
        ['name' => 'Smoke Test Department'],
        ['description' => 'Department untuk smoke test']
    );
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the departments page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/departments')
        ->assertPathIs('/departments')
        ->assertSee('Departemen');

    ScreenshotHelper::saveIfEnabled($page, 'departments-page');
});

it('displays tambah button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/departments')
        ->assertVisible('@bt-tambah');

    ScreenshotHelper::saveIfEnabled($page, 'departments-tambah-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/departments')
        ->assertVisible('@datatable-departments');

    ScreenshotHelper::saveIfEnabled($page, 'departments-datatable');
});

it('displays at least one data row in datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/departments')
        ->assertPathIs('/departments')
        ->assertVisible('@datatable-departments');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-departments\"]');
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

    ScreenshotHelper::saveIfEnabled($page, 'departments-datatable-rows');
});

it('displays edit and delete buttons on data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/departments')
        ->assertPathIs('/departments');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-departments\"]');
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

    ScreenshotHelper::saveIfEnabled($page, 'departments-action-buttons');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/departments')
        ->assertPathIs('/departments')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'departments-no-errors');
});
