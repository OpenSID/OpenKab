<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the infrastruktur page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertPathIs('/data-pokok/infrastruktur');

    ScreenshotHelper::saveIfEnabled($page, 'infrastruktur-page');
});

it('displays statistik kondisi transportasi', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertSee('Statistik Kondisi Transportasi');

    ScreenshotHelper::saveIfEnabled($page, 'infrastruktur-kondisi-transportasi');
});

it('displays statistik sanitasi', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertSee('Statistik Sanitasi');

    ScreenshotHelper::saveIfEnabled($page, 'infrastruktur-sanitasi');
});

it('renders all charts successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertPathIs('/data-pokok/infrastruktur');

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const kondisi = document.querySelector(\'[data-testid="chart-transportasi"]\');
                const sanitasi = document.querySelector(\'[data-testid="chart-sanitasi"]\');
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

    ScreenshotHelper::saveIfEnabled($page, 'infrastruktur-charts');
});

it('displays filter tahun', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertVisible('#filter-tahun');

    ScreenshotHelper::saveIfEnabled($page, 'infrastruktur-filter-tahun');
});

it('displays cetak button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertScript(
            "new Promise((resolve) => {
                const check = () => {
                    const btn = document.querySelector('[data-testid=\"bt-cetak\"]') || document.querySelector('#cetak') || document.querySelector('button[data-print-url]');
                    resolve(!!btn);
                };
                check();
            })",
            true
        );

    ScreenshotHelper::saveIfEnabled($page, 'infrastruktur-cetak-button');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertScript(
            "new Promise((resolve) => {
                const check = () => {
                    const btn = document.querySelector('[data-testid=\"bt-excel\"]') || document.querySelector('#export-excel') || document.querySelector('#download-excel') || document.querySelector('button[data-download-url]');
                    resolve(!!btn);
                };
                check();
            })",
            true
        );

    ScreenshotHelper::saveIfEnabled($page, 'infrastruktur-excel-button');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertPathIs('/data-pokok/infrastruktur');

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector(\'[data-testid="datatable-infrastruktur"]\');
                if (table) { resolve(true); } else { setTimeout(check, 500); }
            };
            check();
        })',
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'infrastruktur-datatable');
});

it('has at least 1 data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertPathIs('/data-pokok/infrastruktur');

    $page->assertScript(
        'new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector(\'[data-testid="datatable-infrastruktur"]\');
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

    ScreenshotHelper::saveIfEnabled($page, 'infrastruktur-data-rows');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/infrastruktur')
        ->assertPathIs('/data-pokok/infrastruktur')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'infrastruktur-no-errors');
});
