<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the ketenagakerjaan page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertPathIs('/data-pokok/ketenagakerjaan')
        ->assertSee('Statistik Jumlah Penghasilan')
        ->assertSee('Statistik Pelatihan');

    ScreenshotHelper::saveIfEnabled($page, 'ketenagakerjaan-page');
});

it('displays statistik jumlah penghasilan', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertSee('Statistik Jumlah Penghasilan')
        ->assertVisible('canvas');

    ScreenshotHelper::saveIfEnabled($page, 'ketenagakerjaan-bar-chart');
});

it('displays statistik pelatihan', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertSee('Statistik Pelatihan')
        ->assertVisible('canvas');

    ScreenshotHelper::saveIfEnabled($page, 'ketenagakerjaan-donut-chart');
});

it('renders all charts successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertPathIs('/data-pokok/ketenagakerjaan');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const donut = document.querySelector('canvas');
                const bar = document.querySelectorAll('canvas')[1];
                const donutReady = donut && donut.getContext && donut.width > 0;
                const barReady = bar && bar.getContext && bar.width > 0;
                if (donutReady && barReady) {
                    resolve(true);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'ketenagakerjaan-charts');
});

it('displays cetak button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertVisible('@btn-cetak');

    ScreenshotHelper::saveIfEnabled($page, 'ketenagakerjaan-cetak-button');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertVisible('@btn-export-excel');

    ScreenshotHelper::saveIfEnabled($page, 'ketenagakerjaan-excel-button');
});

it('displays datatable with data rows', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertPathIs('/data-pokok/ketenagakerjaan')
        ->assertVisible('@datatable-ketenagakerjaan');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const rows = document.querySelectorAll('[data-testid=\"datatable-ketenagakerjaan\"] tbody tr');
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

    ScreenshotHelper::saveIfEnabled($page, 'ketenagakerjaan-datatable-rows');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertPathIs('/data-pokok/ketenagakerjaan')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'ketenagakerjaan-no-errors');
});
