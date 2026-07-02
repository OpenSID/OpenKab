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
    $page = SessionState::loginAndNavigate($this->user, '/lembaga')
        ->assertPathIs('/lembaga')
        ->assertSee('Lembaga');

    ScreenshotHelper::saveIfEnabled($page, 'kelembagaan-page');
});

it('displays filter button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/lembaga')
        ->assertVisible('@bt-toggle-filter');

    ScreenshotHelper::saveIfEnabled($page, 'kelembagaan-filter-button');
});

it('displays cetak button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/lembaga')
        ->assertVisible('@bt-cetak');

    ScreenshotHelper::saveIfEnabled($page, 'kelembagaan-cetak-button');
});

it('displays excel button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/lembaga')
        ->assertVisible('@bt-excel');

    ScreenshotHelper::saveIfEnabled($page, 'kelembagaan-excel-button');
});

it('displays datatable with data rows', function () {
    $page = SessionState::loginAndNavigate($this->user, '/lembaga')
        ->assertPathIs('/lembaga')
        ->assertVisible('@datatable-lembaga');

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

    ScreenshotHelper::saveIfEnabled($page, 'kelembagaan-datatable-rows');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/lembaga')
        ->assertPathIs('/lembaga')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'kelembagaan-no-errors');
});
