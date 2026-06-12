<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the bantuan page', function () {
    SessionState::loginAndNavigate($this->user, '/bantuan')
        ->assertPathIs('/bantuan')
        ->assertSee('Bantuan');
});

it('displays filter button', function () {
    SessionState::loginAndNavigate($this->user, '/bantuan')
        ->assertVisible('[href="#collapse-filter"]');
});

it('displays cetak button', function () {
    SessionState::loginAndNavigate($this->user, '/bantuan')
        ->assertVisible('#cetak');
});

it('displays excel button', function () {
    SessionState::loginAndNavigate($this->user, '/bantuan')
        ->assertVisible('#download-excel');
});

it('displays datatable', function () {
    SessionState::loginAndNavigate($this->user, '/bantuan')
        ->assertVisible('#bantuan');
});

it('displays at least one data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/bantuan')
        ->assertPathIs('/bantuan');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const rows = document.querySelectorAll('#bantuan tbody tr');
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

it('displays detail button per row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/bantuan')
        ->assertPathIs('/bantuan');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const rows = document.querySelectorAll('#bantuan tbody tr');
                if (rows.length > 0 && !rows[0].classList.contains('dataTables_empty')) {
                    const detailBtn = document.querySelector('#bantuan tbody td:nth-child(2) a[href*=\"bantuan/detail\"]');
                    resolve(!!detailBtn);
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
    SessionState::loginAndNavigate($this->user, '/bantuan')
        ->assertPathIs('/bantuan')
        ->assertNoJavaScriptErrors();
});
