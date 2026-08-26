<?php

use Tests\Browser\ScreenshotHelper;
use Tests\Browser\SessionState;

beforeEach(function () {
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
        ->assertVisible('@chart-container')
        ->assertVisible('.oc-export-btn');

    ScreenshotHelper::saveIfEnabled($page, 'orgchart-cetak-button');
});

it('has no javascript errors', function () {
    $page = SessionState::loginAndNavigate($this->user, '/orgchart')
        ->assertPathIs('/orgchart')
        ->assertNoJavaScriptErrors();

    ScreenshotHelper::saveIfEnabled($page, 'orgchart-no-errors');
});
