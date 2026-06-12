<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the lembaga page', function () {
    SessionState::loginAndNavigate($this->user, '/lembaga')
        ->assertPathIs('/lembaga')
        ->assertSee('Lembaga');
});

it('displays filter button', function () {
    SessionState::loginAndNavigate($this->user, '/lembaga')
        ->assertVisible('[href="#collapse-filter"]');
});

it('displays cetak button', function () {
    SessionState::loginAndNavigate($this->user, '/lembaga')
        ->assertVisible('#print-btn-table-lembaga');
});

it('displays excel button', function () {
    SessionState::loginAndNavigate($this->user, '/lembaga')
        ->assertVisible('#download-excel');
});

it('displays datatable', function () {
    SessionState::loginAndNavigate($this->user, '/lembaga')
        ->assertVisible('#table-lembaga');
});

it('displays at least one data row', function () {
    $page = SessionState::loginAndNavigate($this->user, '/lembaga')
        ->assertPathIs('/lembaga');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const rows = document.querySelectorAll('#table-lembaga tbody tr');
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
    SessionState::loginAndNavigate($this->user, '/lembaga')
        ->assertPathIs('/lembaga')
        ->assertNoJavaScriptErrors();
});
