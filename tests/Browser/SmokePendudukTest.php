<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the penduduk page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertPathIs('/penduduk')
        ->assertSee('Data Penduduk');

    ScreenshotHelper::saveIfEnabled($page, 'penduduk-page');
});

it('displays filter button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertVisible('[data-testid="bt-toggle-filter"]');

    ScreenshotHelper::saveIfEnabled($page, 'penduduk-filter-button');
});

it('displays cetak button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertVisible('[data-testid="bt-cetak"]');

    ScreenshotHelper::saveIfEnabled($page, 'penduduk-cetak-button');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertVisible('[data-testid="bt-excel"]');

    ScreenshotHelper::saveIfEnabled($page, 'penduduk-excel-button');
});

it('displays datatable with data rows', function () {
    $page = SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertPathIs('/penduduk')
        ->assertVisible('[data-testid="datatable-penduduk"]');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const rows = document.querySelectorAll('#penduduk tbody tr');
                if (rows.length > 0 && !rows[0].classList.contains('dataTables_empty')) {
                    resolve(true);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'penduduk-datatable-rows');
});

it('displays select action button per row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertPathIs('/penduduk');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const rows = document.querySelectorAll('#penduduk tbody tr');
                if (rows.length > 0 && !rows[0].classList.contains('dataTables_empty')) {
                    const firstRow = rows[0];
                    const actionBtn = firstRow.querySelector('.dropdown-toggle, button[data-toggle=\"dropdown\"]');
                    resolve(!!actionBtn);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'penduduk-action-button');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertPathIs('/penduduk')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'penduduk-no-errors');
});
