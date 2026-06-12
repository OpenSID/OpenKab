<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the pendidikan page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertPathIs('/data-pokok/pendidikan');

    ScreenshotHelper::saveIfEnabled($page, 'pendidikan-page');
});

it('displays statistik partisipasi sekolah', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertSee('Statistik Partisipasi Sekolah')
        ->assertVisible('#donutChart');

    ScreenshotHelper::saveIfEnabled($page, 'pendidikan-donut-chart');
});

it('displays statistik ijazah tertinggi', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertSee('Statistik Ijazah Tertinggi')
        ->assertVisible('#barChart');

    ScreenshotHelper::saveIfEnabled($page, 'pendidikan-bar-chart');
});

it('renders all charts successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertPathIs('/data-pokok/pendidikan');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const donut = document.querySelector('#donutChart');
                const bar = document.querySelector('#barChart');
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

    ScreenshotHelper::saveIfEnabled($page, 'pendidikan-charts');
});

it('displays cetak button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertVisible('[data-testid="bt-cetak"]');

    ScreenshotHelper::saveIfEnabled($page, 'pendidikan-cetak-button');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertVisible('[data-testid="bt-excel"]');

    ScreenshotHelper::saveIfEnabled($page, 'pendidikan-excel-button');
});

it('displays datatable with data rows', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertPathIs('/data-pokok/pendidikan')
        ->assertVisible('[data-testid="datatable-pendidikan"]');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const rows = document.querySelectorAll('#pendidikan tbody tr');
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

    ScreenshotHelper::saveIfEnabled($page, 'pendidikan-datatable-rows');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertPathIs('/data-pokok/pendidikan')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'pendidikan-no-errors');
});
