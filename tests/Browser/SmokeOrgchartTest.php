<?php

use Illuminate\Support\Facades\Http;
use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
    Http::fake();
    $this->user = SessionState::loginAdminUser();
});

afterEach(function () {
    SessionState::clear();
});

it('opens the orgchart page', function () {
    $page = SessionState::loginAndNavigate($this->user, '/orgchart')
        ->assertPathIs('/orgchart');

    ScreenshotHelper::saveIfEnabled($page, 'orgchart-page');
});

it('renders the chart container', function () {
    $page = SessionState::loginAndNavigate($this->user, '/orgchart')
        ->assertVisible('@chart-container');

    ScreenshotHelper::saveIfEnabled($page, 'orgchart-container');
});

it('displays cetak button', function () {
    $page = SessionState::loginAndNavigate($this->user, '/orgchart')
        ->assertVisible('@chart-container');

    $page->assertScript(
        "new Promise((resolve) => {
            const check = () => {
                const exportBtn = document.querySelector('[data-testid=\"bt-cetak\"]');
                if (exportBtn) {
                    resolve(true);
                } else {
                    setTimeout(check, 500);
                }
            };
            check();
        })",
        true
    );

    ScreenshotHelper::saveIfEnabled($page, 'orgchart-cetak-button');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/orgchart')
        ->assertPathIs('/orgchart')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'orgchart-no-errors');
});
