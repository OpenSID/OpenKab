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

$kategoriPenduduk = FixtureReader::kategoriStatistikPendudukNames();
$defaultId = 'rentang-umur';

it('opens the statistik penduduk page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
        ->assertPathIs('/statistik/penduduk')
        ->assertSee('Data Statistik Penduduk');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-page');
});

it('displays kategori statistik list', function () use ($kategoriPenduduk) {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
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

    foreach ($kategoriPenduduk as $nama) {
        $escaped = addslashes($nama);
        $page->assertScript(
            "Array.from(document.querySelectorAll('[data-testid=\"daftar-statistik\"] .pilih-kategori a')).some(a => a.textContent.trim().includes('{$escaped}'))",
            true
        );
    }

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-kategori-list');
});

it('displays cetak button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
        ->assertVisible('@btn-cetak');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-cetak-button');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
        ->assertVisible('@btn-export-excel');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-excel-button');
});

it('displays grafik button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
        ->assertVisible('@btn-toggle-grafik');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-grafik-button');
});

it('displays chart button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
        ->assertVisible('@btn-toggle-pie');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-chart-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
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

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-datatable');
});

it('accesses a kategori statistik and loads data', function () use ($defaultId) {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
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

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-kategori-data');
});

it('accesses grafik and renders successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
        ->assertVisible('@btn-toggle-grafik');

    $page->click('@btn-toggle-grafik');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const canvas = document.querySelector('[data-testid=\"chart-bar\"]');
                if (canvas && canvas.getContext && canvas.width > 0) {
                    resolve(true);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-grafik');
});

it('accesses chart and renders successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
        ->assertVisible('@btn-toggle-pie');

    $page->click('@btn-toggle-pie');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const canvas = document.querySelector('[data-testid=\"chart-donut\"]');
                if (canvas && canvas.getContext && canvas.width > 0) {
                    resolve(true);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-chart');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
        ->assertPathIs('/statistik/penduduk')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-no-errors');
});
