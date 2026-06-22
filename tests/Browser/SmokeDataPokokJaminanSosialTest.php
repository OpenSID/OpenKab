<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the jaminan sosial page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/jaminan-sosial')
        ->assertPathIs('/data-pokok/jaminan-sosial');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-jaminan-sosial-page');
});

it('displays statistik jenis bantuan', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/jaminan-sosial')
        ->assertSee('Statistik Jenis Bantuan');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-jaminan-sosial-jenis-bantuan');
});

it('displays statistik jenis gangguan mental', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/jaminan-sosial')
        ->assertSee('Statistik Jenis Gangguan Mental');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-jaminan-sosial-gangguan-mental');
});

it('displays statistik jenis penanganan', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/jaminan-sosial')
        ->assertSee('Statistik Jenis Penanganan');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-jaminan-sosial-jenis-penanganan');
});

it('renders all charts successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/jaminan-sosial')
        ->assertPathIs('/data-pokok/jaminan-sosial');

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const pie1 = document.querySelector(\'[data-testid=\"chart-pie-bantuan\"]\');
                const pie2 = document.querySelector(\'[data-testid=\"chart-pie-mental\"]\');
                const pie4 = document.querySelector(\'[data-testid=\"chart-pie-penanganan\"]\');
                const ready1 = pie1 && pie1.offsetWidth > 0;
                const ready2 = pie2 && pie2.offsetWidth > 0;
                const ready4 = pie4 && pie4.offsetWidth > 0;
                if (ready1 && ready2 && ready4) {
                    resolve(true);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })',
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-jaminan-sosial-charts');
});

it('displays filter tahun', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/jaminan-sosial')
        ->assertVisible('@filter-tahun');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-jaminan-sosial-filter-tahun');
});

it('displays filter status', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/jaminan-sosial')
        ->assertVisible('@filter-status-kelengkapan');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-jaminan-sosial-filter-status');
});

it('displays cetak button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/jaminan-sosial')
        ->assertVisible('@btn-cetak');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-jaminan-sosial-cetak-button');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/jaminan-sosial')
        ->assertVisible('@btn-export-excel');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-jaminan-sosial-excel-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/jaminan-sosial')
        ->assertPathIs('/data-pokok/jaminan-sosial')
        ->assertVisible('@datatable-data-pokok-jaminan-sosial');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-jaminan-sosial-datatable');
});

it('has at least 1 data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/jaminan-sosial')
        ->assertPathIs('/data-pokok/jaminan-sosial');

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector(\'[data-testid=\"datatable-data-pokok-jaminan-sosial\"]\');
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

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-jaminan-sosial-data-rows');
});

it('has detail button in data', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/jaminan-sosial')
        ->assertPathIs('/data-pokok/jaminan-sosial');

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector(\'[data-testid=\"datatable-data-pokok-jaminan-sosial\"]\');
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

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-jaminan-sosial-detail-button');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/jaminan-sosial')
        ->assertPathIs('/data-pokok/jaminan-sosial')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-jaminan-sosial-no-errors');
});
