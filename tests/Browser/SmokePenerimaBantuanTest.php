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
    $page = SessionState::loginAndNavigate($this->user, '/bantuan')
        ->assertPathIs('/bantuan')
        ->assertSee('Bantuan');

    ScreenshotHelper::saveIfEnabled($page, 'bantuan-page');
});

it('displays filter button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/bantuan')
        ->assertVisible('@bt-toggle-filter');

    ScreenshotHelper::saveIfEnabled($page, 'bantuan-filter-button');
});

it('displays cetak button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/bantuan')
        ->assertVisible('@bt-cetak');

    ScreenshotHelper::saveIfEnabled($page, 'bantuan-cetak-button');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/bantuan')
        ->assertVisible('@bt-excel');

    ScreenshotHelper::saveIfEnabled($page, 'bantuan-excel-button');
});

it('displays datatable with data rows', function () {
    $page = SessionState::loginAndNavigate($this->user, '/bantuan')
        ->assertPathIs('/bantuan')
        ->assertVisible('@datatable-bantuan');

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

    ScreenshotHelper::saveIfEnabled($page, 'bantuan-datatable-rows');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/bantuan')
        ->assertPathIs('/bantuan')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'bantuan-no-errors');
});
