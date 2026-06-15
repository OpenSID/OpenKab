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
                const items = document.querySelectorAll('#daftar-statistik .pilih-kategori');
                if (items.length > 0) { resolve(true); } else { setTimeout(check, 500); }
            };
            check();
        })",
        true
    );

    foreach ($kategoriPenduduk as $nama) {
        $escaped = addslashes($nama);
        $page->assertScript(
            "Array.from(document.querySelectorAll('#daftar-statistik .pilih-kategori a')).some(a => a.textContent.trim().includes('{$escaped}'))",
            true
        );
    }

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-kategori-list');
});

it('displays cetak button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
        ->assertVisible('@bt-cetak');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-cetak-button');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
        ->assertVisible('@bt-excel');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-excel-button');
});

it('displays grafik button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
        ->assertVisible('@bt-grafik');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-grafik-button');
});

it('displays chart button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
        ->assertVisible('@bt-chart');

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-chart-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
        ->assertVisible('@datatable-statistik');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const rows = document.querySelectorAll('#tabel-data tbody tr');
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
                const items = document.querySelectorAll('#daftar-statistik .pilih-kategori');
                if (items.length > 0) { resolve(true); } else { setTimeout(check, 500); }
            };
            check();
        })",
        true
    );

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const rows = document.querySelectorAll('#tabel-data tbody tr');
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
        ->assertVisible('@bt-grafik');

    $page->click('@bt-grafik');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const container = document.querySelector('#grafik-statistik');
                const canvas = document.querySelector('#barChart');
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

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-grafik');
});

it('accesses chart and renders successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
        ->assertVisible('@bt-chart');

    $page->click('@bt-chart');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const container = document.querySelector('#pie-statistik');
                const canvas = document.querySelector('#donutChart');
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

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-chart');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/statistik/penduduk')
        ->assertPathIs('/statistik/penduduk')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'statistik-penduduk-no-errors');
});
