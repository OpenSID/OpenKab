<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the ketenagakerjaan page', function () {
    SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertPathIs('/data-pokok/ketenagakerjaan')
        ->assertSee('Statistik Jumlah Penghasilan')
        ->assertSee('Statistik Pelatihan');
});

it('displays statistik jumlah penghasilan', function () {
    SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertSee('Statistik Jumlah Penghasilan')
        ->assertVisible('#barChart');
});

it('displays statistik pelatihan', function () {
    SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertSee('Statistik Pelatihan')
        ->assertVisible('#donutChart');
});

it('renders all charts successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertPathIs('/data-pokok/ketenagakerjaan');

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
    SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertVisible('#print-btn-ketenagakerjaan');
});

it('displays excel button', function () {
    SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertVisible('#download-excel');
});

it('displays datatable', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertPathIs('/data-pokok/ketenagakerjaan');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const table = document.querySelector('#ketenagakerjaan');
                resolve(!!table);
            };
            check();
        })",
        true
    );
});

it('displays at least one data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertPathIs('/data-pokok/ketenagakerjaan');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const rows = document.querySelectorAll('#ketenagakerjaan tbody tr');
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
    SessionState::loginAndNavigate($this->user, '/data-pokok/ketenagakerjaan')
        ->assertPathIs('/data-pokok/ketenagakerjaan')
        ->assertNoJavaScriptErrors();
});
