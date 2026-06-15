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

$kategoriKeluarga = FixtureReader::kategoriStatistikKeluargaNames();
$defaultId = 'kelas-sosial';

it('opens the statistik keluarga page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/keluarga')
        ->assertPathIs('/statistik/keluarga')
        ->assertSee('Data Statistik Keluarga');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-keluarga-page');
});

it('displays kategori statistik list', function () use ($kategoriKeluarga) {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/keluarga')
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

    foreach ($kategoriKeluarga as $nama) {
        $escaped = addslashes($nama);
        $page->assertScript(
            "Array.from(document.querySelectorAll('[data-testid=\"daftar-statistik\"] .pilih-kategori a')).some(a => a.textContent.trim().includes('{$escaped}'))",
            true
        );
    }

    ScreenshotHelper::saveIfEnabled($page, 'statistik-keluarga-kategori-list');
});

it('displays cetak button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/keluarga')
        ->assertVisible('@btn-cetak');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-keluarga-cetak-button');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/keluarga')
        ->assertVisible('@btn-export-excel');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-keluarga-excel-button');
});

it('displays grafik button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/keluarga')
        ->assertVisible('@btn-toggle-grafik');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-keluarga-grafik-button');
});

it('displays chart button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/keluarga')
        ->assertVisible('@btn-toggle-pie');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-keluarga-chart-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/keluarga')
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

    ScreenshotHelper::saveIfEnabled($page, 'statistik-keluarga-datatable');
});

it('accesses a kategori statistik and loads data', function () use ($defaultId) {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/keluarga')
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

    ScreenshotHelper::saveIfEnabled($page, 'statistik-keluarga-kategori-data');
});

it('accesses grafik and renders successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/keluarga')
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

    ScreenshotHelper::saveIfEnabled($page, 'statistik-keluarga-grafik');
});

it('accesses chart and renders successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/keluarga')
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

    ScreenshotHelper::saveIfEnabled($page, 'statistik-keluarga-chart');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/keluarga')
        ->assertPathIs('/statistik/keluarga')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'statistik-keluarga-no-errors');
});
