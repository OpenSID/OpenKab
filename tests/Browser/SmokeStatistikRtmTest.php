<?php

use Tests\Browser\FixtureReader;
use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

$kategoriRtm = FixtureReader::kategoriStatistikRtmNames();
$defaultId = 'bdt';

it('opens the statistik rtm page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/rtm')
        ->assertPathIs('/statistik/rtm')
        ->assertSee('Data Statistik RTM');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-rtm-page');
});

it('displays kategori statistik list', function () use ($kategoriRtm) {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/rtm')
        ->assertVisible('@daftar-statistik');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const items = document.querySelectorAll('[data-testid=\"daftar-statistik\"] .pilih-kategori');
                if (items.length > 0) { resolve(true); } else { setTimeout(check, 500); }
            };
            check();
        })",
        true
    );

    foreach ($kategoriRtm as $nama) {
        $escaped = addslashes($nama);
        $page->assertScript(
            "Array.from(document.querySelectorAll('[data-testid=\"daftar-statistik\"] .pilih-kategori a')).some(a => a.textContent.trim().includes('{$escaped}'))",
            true
        );
    }

    ScreenshotHelper::saveIfEnabled($page, 'statistik-rtm-kategori-list');
});

it('displays cetak button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/rtm')
        ->assertVisible('@btn-cetak');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-rtm-cetak-button');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/rtm')
        ->assertVisible('@btn-export-excel');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-rtm-excel-button');
});

it('displays grafik button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/rtm')
        ->assertVisible('@btn-toggle-grafik');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-rtm-grafik-button');
});

it('displays chart button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/rtm')
        ->assertVisible('@btn-toggle-pie');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-rtm-chart-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/rtm')
        ->assertVisible('@datatable-statistik');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const rows = document.querySelectorAll('[data-testid=\"datatable-statistik\"] tbody tr');
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

    ScreenshotHelper::saveIfEnabled($page, 'statistik-rtm-datatable');
});

it('accesses a kategori statistik and loads data', function () use ($defaultId) {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/rtm')
        ->assertVisible('@daftar-statistik');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const items = document.querySelectorAll('[data-testid=\"daftar-statistik\"] .pilih-kategori');
                if (items.length > 0) { resolve(true); } else { setTimeout(check, 500); }
            };
            check();
        })",
        true
    );

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const rows = document.querySelectorAll('[data-testid=\"datatable-statistik\"] tbody tr');
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

    ScreenshotHelper::saveIfEnabled($page, 'statistik-rtm-kategori-data');
});

it('accesses grafik and renders successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/rtm')
        ->assertVisible('@btn-toggle-grafik');

    $page->click('@btn-toggle-grafik');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const container = document.querySelector('#grafik-statistik');
                const canvas = document.querySelector('[data-testid=\"chart-bar\"]');
                if (container && canvas && canvas.getContext && canvas.width > 0) {
                    resolve(true);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'statistik-rtm-grafik');
});

it('accesses chart and renders successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/rtm')
        ->assertVisible('@btn-toggle-pie');

    $page->click('@btn-toggle-pie');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const container = document.querySelector('#pie-statistik');
                const canvas = document.querySelector('[data-testid=\"chart-donut\"]');
                if (container && canvas && canvas.getContext && canvas.width > 0) {
                    resolve(true);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'statistik-rtm-chart');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/rtm')
        ->assertPathIs('/statistik/rtm')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'statistik-rtm-no-errors');
});
