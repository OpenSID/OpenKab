<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the penduduk page', function () {
    SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertPathIs('/penduduk')
        ->assertSee('Data Penduduk');
});

it('displays filter button', function () {
    SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertVisible('[href="#collapse-filter"]');
});

it('displays cetak button', function () {
    SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertVisible('#cetak');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertPathIs('/penduduk');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const btn = document.querySelector('#download-excel') || document.querySelector('button[data-download-url]');
                resolve(!!btn);
            };
            check();
        })",
        true
    );
});

it('displays datatable', function () {
    SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertVisible('#penduduk');
});

it('displays at least one data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertPathIs('/penduduk');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const rows = document.querySelectorAll('#penduduk tbody tr');
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
});

it('displays select aksi button per row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertPathIs('/penduduk');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const rows = document.querySelectorAll('#penduduk tbody tr');
                if (rows.length > 0 && !rows[0].classList.contains('dataTables_empty')) {
                    const hasAction = document.querySelector('#penduduk tbody td:nth-child(2) a, #penduduk tbody td:nth-child(2) button, #penduduk tbody td:nth-child(2) .dropdown');
                    resolve(!!hasAction);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })",
        true
    );
});

it('has no javascript errors', function () {
    SessionState::loginAndNavigate($this->user, '/penduduk')
        ->assertPathIs('/penduduk')
        ->assertNoJavaScriptErrors();
});
