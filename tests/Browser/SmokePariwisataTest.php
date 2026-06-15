<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the pariwisata page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pariwisata')
        ->assertPathIs('/data-pokok/pariwisata');

    ScreenshotHelper::saveIfEnabled($page, 'pariwisata-page');
});

it('displays statistik jumlah penginapan', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pariwisata')
        ->assertSee('Statistik Jumlah Penginapan');

    ScreenshotHelper::saveIfEnabled($page, 'pariwisata-jumlah-penginapan');
});

it('displays statistik tingkat pemanfaatan', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pariwisata')
        ->assertSee('Statistik Tingkat Pemanfaatan');

    ScreenshotHelper::saveIfEnabled($page, 'pariwisata-tingkat-pemanfaatan');
});

it('renders all charts successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pariwisata')
        ->assertPathIs('/data-pokok/pariwisata');

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const bar = document.querySelector(\'[data-testid=\"chart-bar-penginapan\"]\');
                const donut = document.querySelector(\'[data-testid=\"chart-donut-pemanfaatan\"]\');
                const barReady = bar && bar.getContext && bar.width > 0;
                const donutReady = donut && donut.getContext && donut.width > 0;
                if (barReady && donutReady) {
                    resolve(true);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })',
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'pariwisata-charts');
});

it('displays filter tahun', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pariwisata')
        ->assertVisible('@filter-tahun');

    ScreenshotHelper::saveIfEnabled($page, 'pariwisata-filter-tahun');
});

it('displays filter kategori', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pariwisata')
        ->assertVisible('@filter-kategori-wisata');

    ScreenshotHelper::saveIfEnabled($page, 'pariwisata-filter-kategori');
});

it('displays cetak button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pariwisata')
        ->assertVisible('@btn-cetak');

    ScreenshotHelper::saveIfEnabled($page, 'pariwisata-cetak-button');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pariwisata')
        ->assertVisible('@btn-export-excel');

    ScreenshotHelper::saveIfEnabled($page, 'pariwisata-excel-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pariwisata')
        ->assertPathIs('/data-pokok/pariwisata')
        ->assertVisible('@datatable-pariwisata');

    ScreenshotHelper::saveIfEnabled($page, 'pariwisata-datatable');
});

it('has at least 1 data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pariwisata')
        ->assertPathIs('/data-pokok/pariwisata');

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector(\'[data-testid=\"datatable-pariwisata\"]\');
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

    ScreenshotHelper::saveIfEnabled($page, 'pariwisata-data-rows');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pariwisata')
        ->assertPathIs('/data-pokok/pariwisata')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'pariwisata-no-errors');
});
