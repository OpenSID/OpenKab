<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

$path = '/data-presisi/statistik/ketenagakerjaan';

it('opens the statistik ketenagakerjaan page', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertPathIs($path)
        ->assertSee('Ketenagakerjaan');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-ketenagakerjaan-page');
});

it('displays kategori statistik list', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertVisible('@daftar-statistik');

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const items = document.querySelectorAll(\'[data-testid="daftar-statistik"] a\');
                if (items.length > 0) {
                    resolve(true);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })',
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'statistik-ketenagakerjaan-kategori-list');
});

it('displays filter tahun', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertVisible('@filter-tahun');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-ketenagakerjaan-filter-tahun');
});

it('displays excel button', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertVisible('@btn-export-excel');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-ketenagakerjaan-excel-button');
});

it('displays grafik button', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertVisible('@btn-toggle-grafik');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-ketenagakerjaan-grafik-button');
});

it('displays chart button', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertVisible('@btn-toggle-pie');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-ketenagakerjaan-chart-button');
});

it('displays datatable', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertVisible('@datatable-statistik');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-ketenagakerjaan-datatable');
});

it('datatable displays data', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertPathIs($path);

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector(\'[data-testid="datatable-statistik"]\');
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

    ScreenshotHelper::saveIfEnabled($page, 'statistik-ketenagakerjaan-datatable-data');
});

it('clicks a kategori statistik', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertVisible('@daftar-statistik');

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const items = document.querySelectorAll(\'[data-testid="daftar-statistik"] a\');
                if (items.length > 0) {
                    items[0].click();
                    resolve(true);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })',
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'statistik-ketenagakerjaan-kategori-clicked');
});

it('renders bar chart', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertPathIs($path);

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const bar = document.querySelector(\'[data-testid="chart-bar"]\');
                const ready = bar && bar.getContext && bar.width > 0;
                if (ready) {
                    resolve(true);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })',
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'statistik-ketenagakerjaan-bar-chart');
});

it('renders pie chart', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertPathIs($path);

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const donut = document.querySelector(\'[data-testid="chart-donut"]\');
                const ready = donut && donut.getContext && donut.width > 0;
                if (ready) {
                    resolve(true);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })',
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'statistik-ketenagakerjaan-pie-chart');
});

it('has no javascript errors', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertPathIs($path)
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'statistik-ketenagakerjaan-no-errors');
});
