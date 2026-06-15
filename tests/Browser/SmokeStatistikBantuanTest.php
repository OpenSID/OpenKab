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

$kategoriBantuan = FixtureReader::kategoriStatistikBantuanNames();
$defaultId = 'penduduk';

it('opens the statistik bantuan page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/bantuan')
        ->assertPathIs('/statistik/bantuan')
        ->assertSee('Data Statistik Bantuan');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-bantuan-page');
});

it('displays kategori statistik list', function () use ($kategoriBantuan) {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/bantuan')
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

    foreach ($kategoriBantuan as $nama) {
        $escaped = addslashes($nama);
        $page->assertScript(
            "Array.from(document.querySelectorAll('[data-testid=\"daftar-statistik\"] .pilih-kategori a')).some(a => a.textContent.trim().includes('{$escaped}'))",
            true
        );
    }

    ScreenshotHelper::saveIfEnabled($page, 'statistik-bantuan-kategori-list');
});

it('displays cetak button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/bantuan')
        ->assertVisible('@btn-cetak');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-bantuan-cetak-button');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/bantuan')
        ->assertVisible('@btn-export-excel');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-bantuan-excel-button');
});

it('displays grafik button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/bantuan')
        ->assertVisible('@btn-toggle-grafik');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-bantuan-grafik-button');
});

it('displays chart button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/bantuan')
        ->assertVisible('@btn-toggle-pie');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-bantuan-chart-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/bantuan')
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

    ScreenshotHelper::saveIfEnabled($page, 'statistik-bantuan-datatable');
});

it('accesses a kategori statistik and loads data', function () use ($defaultId) {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/bantuan')
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

    ScreenshotHelper::saveIfEnabled($page, 'statistik-bantuan-kategori-data');
});

it('accesses grafik and renders successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/bantuan')
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

    ScreenshotHelper::saveIfEnabled($page, 'statistik-bantuan-grafik');
});

it('accesses chart and renders successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/bantuan')
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

    ScreenshotHelper::saveIfEnabled($page, 'statistik-bantuan-chart');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/bantuan')
        ->assertPathIs('/statistik/bantuan')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'statistik-bantuan-no-errors');
});
