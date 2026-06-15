<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the laporan pengisian per desa page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-presisi/laporan/perdesa')
        ->assertPathIs('/data-presisi/laporan/perdesa');

    ScreenshotHelper::saveIfEnabled($page, 'laporan-pengisian-perdesa-page');
});

it('displays cetak button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-presisi/laporan/perdesa')
        ->assertVisible('[data-testid="btn-cetak"]');

    ScreenshotHelper::saveIfEnabled($page, 'laporan-pengisian-perdesa-cetak-button');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-presisi/laporan/perdesa')
        ->assertVisible('[data-testid="btn-export-excel"]');

    ScreenshotHelper::saveIfEnabled($page, 'laporan-pengisian-perdesa-excel-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-presisi/laporan/perdesa')
        ->assertPathIs('/data-presisi/laporan/perdesa');

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector(\'[data-testid="datatable-laporan-perdesa"]\');
                if (table) { resolve(true); } else { setTimeout(check, 500); }
            };
            check();
        })',
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'laporan-pengisian-perdesa-datatable');
});

it('has at least 1 data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-presisi/laporan/perdesa')
        ->assertPathIs('/data-presisi/laporan/perdesa');

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector(\'[data-testid="datatable-laporan-perdesa"]\');
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

    ScreenshotHelper::saveIfEnabled($page, 'laporan-pengisian-perdesa-data-rows');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-presisi/laporan/perdesa')
        ->assertPathIs('/data-presisi/laporan/perdesa')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'laporan-pengisian-perdesa-no-errors');
});
