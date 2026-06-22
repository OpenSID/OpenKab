<?php

use App\Models\Position;
use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    Position::firstOrCreate(
        ['name' => 'Smoke Test Position'],
        ['description' => 'Jabatan untuk smoke test']
    );
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the positions page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/positions')
        ->assertPathIs('/positions')
        ->assertSee('Jabatan');

    ScreenshotHelper::saveIfEnabled($page, 'positions-page');
});

it('displays tambah button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/positions')
        ->assertVisible('@bt-tambah');

    ScreenshotHelper::saveIfEnabled($page, 'positions-tambah-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/positions')
        ->assertVisible('@datatable-positions');

    ScreenshotHelper::saveIfEnabled($page, 'positions-datatable');
});

it('displays at least one data row in datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/positions')
        ->assertPathIs('/positions')
        ->assertVisible('@datatable-positions');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-positions\"]');
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

    ScreenshotHelper::saveIfEnabled($page, 'positions-datatable-rows');
});

it('displays edit and delete buttons on data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/positions')
        ->assertPathIs('/positions');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-positions\"]');
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

    ScreenshotHelper::saveIfEnabled($page, 'positions-action-buttons');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/positions')
        ->assertPathIs('/positions')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'positions-no-errors');
});
