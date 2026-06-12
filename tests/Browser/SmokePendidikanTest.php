<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the pendidikan page', function () {
    SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertPathIs('/data-pokok/pendidikan');
});

it('displays statistik partisipasi sekolah', function () {
    SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertSee('Statistik Partisipasi Sekolah')
        ->assertVisible('#donutChart');
});

it('displays statistik ijazah tertinggi', function () {
    SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertSee('Statistik Ijazah Tertinggi')
        ->assertVisible('#barChart');
});

it('renders all charts successfully', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertPathIs('/data-pokok/pendidikan');

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
    SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertVisible('#print-btn-pendidikan');
});

it('displays excel button', function () {
    SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertVisible('#download-excel');
});

it('displays datatable', function () {
    SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertVisible('#pendidikan');
});

it('displays at least one data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertPathIs('/data-pokok/pendidikan');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const rows = document.querySelectorAll('#pendidikan tbody tr');
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
    SessionState::loginAndNavigate($this->user, '/data-pokok/pendidikan')
        ->assertPathIs('/data-pokok/pendidikan')
        ->assertNoJavaScriptErrors();
});
