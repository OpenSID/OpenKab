<?php

use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $department = Department::firstOrCreate(
        ['name' => 'Smoke Test Department'],
        ['description' => 'Department untuk smoke test']
    );
    $position = Position::firstOrCreate(
        ['name' => 'Smoke Test Position'],
        ['description' => 'Jabatan untuk smoke test']
    );
    Employee::firstOrCreate(
        ['name' => 'Smoke Test Employee'],
        [
            'identity_number' => '1234567890123456',
            'email' => 'smoke.test.employee@mail.test',
            'description' => 'Pejabat untuk smoke test',
            'phone' => '081234567890',
            'department_id' => $department->id,
            'position_id' => $position->id,
        ]
    );
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the employees page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/employees')
        ->assertPathIs('/employees')
        ->assertSee('Pejabat Daerah');

    ScreenshotHelper::saveIfEnabled($page, 'employees-page');
});

it('displays tambah button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/employees')
        ->assertVisible('@bt-tambah');

    ScreenshotHelper::saveIfEnabled($page, 'employees-tambah-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/employees')
        ->assertVisible('@datatable-employees');

    ScreenshotHelper::saveIfEnabled($page, 'employees-datatable');
});

it('displays at least one data row in datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/employees')
        ->assertPathIs('/employees')
        ->assertVisible('@datatable-employees');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-employees\"]');
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

    ScreenshotHelper::saveIfEnabled($page, 'employees-datatable-rows');
});

it('displays edit and delete buttons on data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/employees')
        ->assertPathIs('/employees');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-employees\"]');
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

    ScreenshotHelper::saveIfEnabled($page, 'employees-action-buttons');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/employees')
        ->assertPathIs('/employees')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'employees-no-errors');
});
