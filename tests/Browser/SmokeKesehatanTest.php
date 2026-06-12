<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the kesehatan page', function () {
    SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertPathIs('/data-pokok/kesehatan');
});

it('displays statistik golongan darah', function () {
    SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertSee('Statistik Golongan Darah')
        ->assertVisible('#donutChart');
});

it('displays statistik status gizi balita', function () {
    SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertSee('Statistik Status Gizi Balita')
        ->assertVisible('#barChart');
});

it('renders all charts successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertPathIs('/data-pokok/kesehatan');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const donut = document.querySelector('#donutChart');
                const bar = document.querySelector('#barChart');
                const donutReady = donut && donut.getContext && donut.width > 0;
                const barReady = bar && bar.getContext && bar.width > 0;
                if (donutReady && barReady) {
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

it('displays cetak button', function () {
    SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertVisible('#print-btn-kesehatan');
});

it('displays excel button', function () {
    SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertVisible('#download-excel');
});

it('displays datatable with data', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertPathIs('/data-pokok/kesehatan');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const rows = document.querySelectorAll('#kesehatan tbody tr');
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

it('has no javascript errors', function () {
    SessionState::loginAndNavigate($this->user, '/data-pokok/kesehatan')
        ->assertPathIs('/data-pokok/kesehatan')
        ->assertNoJavaScriptErrors();
});
