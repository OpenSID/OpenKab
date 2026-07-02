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

it('opens the riwayat pengguna page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/activities')
        ->assertPathIs('/pengaturan/activities')
        ->assertSee('Data Riwayat Pengguna');

    ScreenshotHelper::saveIfEnabled($page, 'riwayat-page');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/activities')
        ->assertVisible('@datatable-riwayat');

    ScreenshotHelper::saveIfEnabled($page, 'riwayat-datatable');
});

it('displays at least 1 data row in datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/activities')
        ->assertPathIs('/pengaturan/activities')
        ->assertVisible('@datatable-riwayat');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-riwayat\"]');
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

    ScreenshotHelper::saveIfEnabled($page, 'riwayat-datatable-rows');
});

it('can view detail data', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/activities')
        ->assertPathIs('/pengaturan/activities')
        ->assertVisible('@datatable-riwayat');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('[data-testid=\"datatable-riwayat\"]');
                if (table) {
                    const rows = table.querySelectorAll('tbody tr');
                    if (rows.length > 0 && !rows[0].classList.contains('dataTables_empty')) {
                        const firstRow = rows[0];
                        const tree = firstRow.querySelector('.tree');
                        resolve(!!tree);
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

    ScreenshotHelper::saveIfEnabled($page, 'riwayat-detail-data');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/pengaturan/activities')
        ->assertPathIs('/pengaturan/activities')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'riwayat-no-errors');
});
