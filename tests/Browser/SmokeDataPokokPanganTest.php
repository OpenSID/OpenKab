<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

$path = '/data-presisi/pangan';

it('opens the pangan page', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertPathIs($path);

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-pangan-page');
});

it('renders chart', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertPathIs($path);

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const chart = document.querySelector(\'canvas\');
                const ready = chart && chart.getContext && chart.width > 0;
                if (ready) { resolve(true); } else { setTimeout(check, 500); }
            };
            check();
        })',
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-pangan-chart');
});

it('displays filter tahun', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertVisible('@filter-tahun');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-pangan-filter-tahun');
});

it('displays filter status', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertVisible('@filter-status-kelengkapan');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-pangan-filter-status');
});

it('displays cetak button', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertVisible('@btn-cetak');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-pangan-cetak-button');
});

it('displays excel button', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertVisible('@btn-export-excel');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-pangan-excel-button');
});

it('displays datatable', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertPathIs($path)
        ->assertVisible('@datatable-pangan');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-pangan-datatable');
});

it('datatable displays data', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertPathIs($path);

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector(\'[data-testid=\"datatable-pangan\"]\');
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

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-pangan-datatable-data');
});

it('has detail button in data', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertPathIs($path);

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector(\'[data-testid=\"datatable-pangan\"]\');
                if (table) {
                    const rows = table.querySelectorAll(\'tbody tr\');
                    if (rows.length > 0 && !rows[0].classList.contains(\'dataTables_empty\')) {
                        const detailBtn = rows[0].querySelector(\'[data-button="Detail"]\') || rows[0].querySelector(\'a[href*="detail"]\');
                        resolve(!!detailBtn);
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

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-pangan-detail-button');
});

it('has no javascript errors', function () use ($path) {
    $page = SessionState::loginAndNavigate($this->user, $path)
        ->assertPathIs($path)
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-pangan-no-errors');
});
