<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the data-pokok-infrastruktur page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertPathIs('/data-pokok/infrastruktur');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-infrastruktur-page');
});

it('displays statistik kondisi transportasi', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertSee('Statistik Kondisi Transportasi');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-infrastruktur-kondisi-transportasi');
});

it('displays statistik sanitasi', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertSee('Statistik Sanitasi');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-infrastruktur-sanitasi');
});

it('renders all charts successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertPathIs('/data-pokok/infrastruktur');

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const kondisi = document.querySelector(\'[data-testid=\"chart-transportasi\"]\');
                const sanitasi = document.querySelector(\'[data-testid=\"chart-sanitasi\"]\');
                const kondisiReady = kondisi && kondisi.getContext && kondisi.width > 0;
                const sanitasiReady = sanitasi && sanitasi.getContext && sanitasi.width > 0;
                if (kondisiReady && sanitasiReady) {
                    resolve(true);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })',
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-infrastruktur-charts');
});

it('displays filter tahun', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertVisible('@filter-tahun');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-infrastruktur-filter-tahun');
});

it('displays cetak button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertScript(
            "new Promise((resolve) => {
                const check = () => {
                    const btn = document.querySelector('[data-testid=\"btn-cetak\"]') || document.querySelector('button[data-print-url]');
                    resolve(!!btn);
                };
                check();
            })",
            true
        );

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-infrastruktur-cetak-button');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertScript(
            "new Promise((resolve) => {
                const check = () => {
                    const btn = document.querySelector('[data-testid=\"btn-export-excel\"]') || document.querySelector('button[data-download-url]');
                    resolve(!!btn);
                };
                check();
            })",
            true
        );

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-infrastruktur-excel-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertPathIs('/data-pokok/infrastruktur')
        ->assertVisible('@datatable-data-pokok-infrastruktur');

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-infrastruktur-datatable');
});

it('has at least 1 data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertPathIs('/data-pokok/infrastruktur');

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector(\'[data-testid=\"datatable-data-pokok-infrastruktur\"]\');
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

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-infrastruktur-data-rows');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertPathIs('/data-pokok/infrastruktur')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'data-pokok-infrastruktur-no-errors');
});
