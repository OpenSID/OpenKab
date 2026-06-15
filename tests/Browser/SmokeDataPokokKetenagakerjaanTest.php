<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

$path = '/data-pokok/ketenagakerjaan';

it('opens the ketenagakerjaan page', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertPathIs($path);

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-ketenagakerjaan-page');
});

it('renders chart', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertPathIs($path);

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const chart1 = document.querySelector(\'[data-testid="chart-bar"]\');
                const ready1 = chart1 && chart1.getContext && chart1.width > 0;
                const chart2 = document.querySelector(\'[data-testid="chart-donut"]\');
                const ready2 = chart2 && chart2.getContext && chart2.width > 0;
                if (ready1 && ready2) { resolve(true); } else { setTimeout(check, 500); }
            };
            check();
        })',
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-ketenagakerjaan-chart');
});

it('displays cetak button', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertVisible('@bt-cetak');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-ketenagakerjaan-cetak-button');
});

it('displays excel button', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertVisible('@bt-excel');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-ketenagakerjaan-excel-button');
});

it('displays datatable', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertPathIs($path)
        ->assertVisible('@datatable-ketenagakerjaan');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-ketenagakerjaan-datatable');
});

it('datatable displays data', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertPathIs($path);

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector(\'[data-testid="datatable-ketenagakerjaan"]\');
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

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-ketenagakerjaan-datatable-data');
});

it('has no javascript errors', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertPathIs($path)
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-ketenagakerjaan-no-errors');
});
