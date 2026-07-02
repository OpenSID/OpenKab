<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the kesehatan page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertPathIs('/data-pokok/kesehatan');

    ScreenshotHelper::saveIfEnabled($page, 'kesehatan-page');
});

it('displays statistik golongan darah', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertSee('Statistik Golongan Darah')
        ->assertVisible('@chart-donut');

    ScreenshotHelper::saveIfEnabled($page, 'kesehatan-donut-chart');
});

it('displays statistik status gizi balita', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertSee('Statistik Status Gizi Balita')
        ->assertVisible('@chart-bar');

    ScreenshotHelper::saveIfEnabled($page, 'kesehatan-bar-chart');
});

it('renders all charts successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertPathIs('/data-pokok/kesehatan');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const donut = document.querySelector('[data-testid=\"chart-donut\"]');
                const bar = document.querySelector('[data-testid=\"chart-bar\"]');
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

    ScreenshotHelper::saveIfEnabled($page, 'kesehatan-charts');
});

it('displays cetak button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertVisible('@btn-cetak');

    ScreenshotHelper::saveIfEnabled($page, 'kesehatan-cetak-button');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertVisible('@btn-export-excel');

    ScreenshotHelper::saveIfEnabled($page, 'kesehatan-excel-button');
});

it('displays datatable with data rows', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertPathIs('/data-pokok/kesehatan')
        ->assertVisible('@datatable-kesehatan');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const rows = document.querySelectorAll('[data-testid=\"datatable-kesehatan\"] tbody tr');
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

    ScreenshotHelper::saveIfEnabled($page, 'kesehatan-datatable-rows');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertPathIs('/data-pokok/kesehatan')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'kesehatan-no-errors');
});
